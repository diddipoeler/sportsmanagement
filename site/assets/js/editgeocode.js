(function () {
    'use strict';

    let activeRequest = null;

    function field(name) {
        return document.getElementById(`jform_${name}`) || document.getElementById(name);
    }

    function fieldValue(name) {
        const element = field(name);
        return element ? String(element.value || '').trim() : '';
    }

    function setFieldValue(name, value) {
        const element = field(name);

        if (element) {
            element.value = value == null ? '' : String(value);
        }
    }

    function setElementValue(id, value) {
        const element = document.getElementById(id);

        if (element) {
            element.value = value == null ? '' : String(value);
        }
    }

    function selectedCountryName() {
        const country = field('country');

        if (!country) {
            return '';
        }

        if (country instanceof HTMLSelectElement && country.selectedIndex >= 0) {
            const option = country.options[country.selectedIndex];

            if (option && option.value !== '') {
                return String(option.textContent || '').trim();
            }
        }

        return fieldValue('country');
    }

    function getAddressString() {
        const parts = [];
        const street = fieldValue('address');
        const city = fieldValue('city') || fieldValue('location');
        const zipcode = fieldValue('zipcode');
        const state = fieldValue('state');
        const country = selectedCountryName();

        if (street) {
            parts.push(street);
        }

        const locality = [zipcode, city].filter(Boolean).join(' ');

        if (locality) {
            parts.push(locality);
        }

        if (state) {
            parts.push(state);
        }

        if (country) {
            parts.push(country);
        }

        return parts.join(', ');
    }

    function clearAdministrativeFields() {
        [
            'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME',
            'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_SHORT_NAME',
            'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME',
            'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_SHORT_NAME',
            'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_LONG_NAME',
            'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_SHORT_NAME',
        ].forEach((id) => setElementValue(id, ''));
    }

    function applyNominatimResult(result) {
        const address = result && typeof result.address === 'object' ? result.address : {};
        const state = address.state || address.region || '';
        const county = address.county || '';
        const district = address.state_district || address.city_district || '';

        setElementValue(
            'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME',
            state
        );
        setElementValue(
            'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME',
            county
        );
        setElementValue(
            'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_LONG_NAME',
            district
        );

        if (state) {
            setFieldValue('state', state);
        }

        if (result.lat != null) {
            setFieldValue('latitude', result.lat);
        }

        if (result.lon != null) {
            setFieldValue('longitude', result.lon);
        }

        if (result.display_name) {
            setFieldValue('geocomplete', result.display_name);
        }
    }

    async function getLatLonOpenStreet() {
        const query = getAddressString();
        setFieldValue('geocomplete', query);

        if (!query) {
            return false;
        }

        clearAdministrativeFields();

        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();

        const url = new URL('https://nominatim.openstreetmap.org/search');
        url.search = new URLSearchParams({
            format: 'jsonv2',
            addressdetails: '1',
            limit: '1',
            q: query,
        }).toString();

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                },
                signal: activeRequest.signal,
            });

            if (!response.ok) {
                throw new Error(`Nominatim request failed with status ${response.status}`);
            }

            const results = await response.json();
            const result = Array.isArray(results) ? results[0] : null;

            if (result) {
                applyNominatimResult(result);
            }
        } catch (error) {
            if (error instanceof DOMException && error.name === 'AbortError') {
                return false;
            }

            console.error('SportsManagement geocoding request failed.', error);
        } finally {
            activeRequest = null;
        }

        return false;
    }

    function setGeoResult(result) {
        if (!result || !Array.isArray(result.address_components)) {
            return;
        }

        let route = '';
        let streetNumber = '';

        result.address_components.forEach((component) => {
            const type = Array.isArray(component.types) ? component.types[0] : '';

            switch (type) {
                case 'route':
                    route = component.long_name || '';
                    break;
                case 'locality':
                case 'postal_town':
                    setFieldValue('city', component.long_name || '');
                    setFieldValue('location', component.long_name || '');
                    break;
                case 'street_number':
                    streetNumber = component.long_name || '';
                    break;
                case 'administrative_area_level_1':
                    setFieldValue('state', component.long_name || '');
                    setElementValue(
                        'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME',
                        component.long_name || ''
                    );
                    setElementValue(
                        'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_SHORT_NAME',
                        component.short_name || ''
                    );
                    break;
                case 'administrative_area_level_2':
                    setElementValue(
                        'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME',
                        component.long_name || ''
                    );
                    setElementValue(
                        'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_SHORT_NAME',
                        component.short_name || ''
                    );
                    break;
                case 'administrative_area_level_3':
                    setElementValue(
                        'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_LONG_NAME',
                        component.long_name || ''
                    );
                    setElementValue(
                        'extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_SHORT_NAME',
                        component.short_name || ''
                    );
                    break;
                case 'postal_code':
                    setFieldValue('zipcode', component.long_name || '');
                    break;
                default:
                    break;
            }
        });

        setFieldValue('address', [route, streetNumber].filter(Boolean).join(' '));

        const location = result.geometry && result.geometry.location;

        if (location) {
            const latitude = typeof location.lat === 'function' ? location.lat() : location.lat;
            const longitude = typeof location.lng === 'function' ? location.lng() : location.lng;

            if (latitude != null) {
                setFieldValue('latitude', latitude);
            }

            if (longitude != null) {
                setFieldValue('longitude', longitude);
            }
        }

        if (result.formatted_address) {
            setFieldValue('geocomplete', result.formatted_address);
        }
    }

    window.getlatlonopenstreet = getLatLonOpenStreet;
    window.getAddresString = getAddressString;
    window.getAddressString = getAddressString;
    window.setGeoResult = setGeoResult;

    document.addEventListener('DOMContentLoaded', () => {
        const query = getAddressString();
        setFieldValue('geocomplete', query);

        const latitude = Number.parseFloat(fieldValue('latitude'));
        const longitude = Number.parseFloat(fieldValue('longitude'));
        const hasCoordinates = Number.isFinite(latitude)
            && Number.isFinite(longitude)
            && (latitude !== 0 || longitude !== 0);

        if (query && !hasCoordinates) {
            getLatLonOpenStreet();
        }
    });
}());
