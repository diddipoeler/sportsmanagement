var simplemaps_worldmap_mapdata={
  main_settings: {
    //General settings
		//width: "700", //or 'responsive'
		
		width: "responsive",
    background_color: "#FFFFFF",
    background_transparent: "yes",
    popups: "detect",
    
		//State defaults
		state_description: "State description",
    state_color: "#88A4BC",
    state_hover_color: "#3B729F",
   // state_url: "https://simplemaps.com",
    border_size: 1.5,
    border_color: "#ffffff",
    all_states_inactive: "no",
    all_states_zoomable: "no",
    
		//Location defaults
		location_description: "Location description",
    location_color: "#FF0067",
    location_opacity: 0.8,
    location_hover_opacity: 1,
    location_url: "",
    location_size: 25,
    location_type: "square",
    location_border_color: "#FFFFFF",
    location_border: 2,
    location_hover_border: 2.5,
    all_locations_inactive: "no",
    all_locations_hidden: "no",
    
		//Label defaults
		label_color: "#ffffff",
    label_hover_color: "#ffffff",
    label_size: 22,
    label_font: "Arial",
    hide_labels: "no",
   
		//Zoom settings
		manual_zoom: "no",
    back_image: "no",
    arrow_box: "no",
    navigation_size: "40",
    navigation_color: "#f7f7f7",
    navigation_border_color: "#636363",
    initial_back: "no",
    initial_zoom: -1,
    initial_zoom_solo: "no",
    region_opacity: 1,
    region_hover_opacity: 0.6,
    zoom_out_incrementally: "yes",
    zoom_percentage: 0.99,
    zoom_time: 0.5,
    
		//Popup settings
		popup_color: "white",
    popup_opacity: 0.9,
    popup_shadow: 1,
    popup_corners: 5,
    popup_font: "12px/1.5 Verdana, Arial, Helvetica, sans-serif",
    popup_nocss: "no",
    
		//Advanced settings
		div: "map",
    auto_load: "yes",
    rotate: "0",
    url_new_tab: "no",
    images_directory: "default",
    import_labels: "no",
    fade_time: 0.1,
    link_text: "View Website"
  },
regions: {
	0: {
		name: "North America",
		states: ["MX","CA","US","GL"]
	},
	1: {
		name: "South America",
		states: ["EC","AR","VE","BR","CO","BO","PE","BZ","CL","CR","CU","DO","SV","GT","GY","GF","HN","NI","PA","PY","PR","SR","UY","JM","HT","BS"]
	},
	2: {
		name: "Europe",
		states:["IT","NL","NO","DK","IE","GB","RO","DE","FR","AL","AM","AT","BY","BE","LU","BG","CZ","EE","GE","GR","HU","IS","LV","LT","MD","PL","PT","RS","SI","HR","BA","ME","MK","SK","ES","FI","SE","CH","TR","CY","UA","XK"]
	},
	3: {
		name: "Africa and the Middle East",
		states: ["QA","SA","AE","SY","OM","KW","PK","AZ","AF","IR","IQ","IL","PS","JO","LB","YE","TJ","TM","UZ","KG","NE","AO","EG","TN","GA","DZ","LY","CG","GQ","BJ","BW","BF","BI","CM","CF","TD","CI","CD","DJ","ET","GM","GH","GN","GW","KE","LS","LR","MG","MW","ML","MA","MR","MZ","NA","NG","ER","RW","SN","SL","SO","ZA","SD","SS","SZ","TZ","TG","UG","EH","ZM","ZW"]
	},
	4: {
		name: "South Asia",
		states:["TW","IN","AU","MY","NP","NZ","TH","BN","JP","VN","LK","SB","FJ","BD","BT","KH","LA","MM","KP","PG","PH","KR","ID","CN"]
	},
	5:	{
		name: "North Asia",
		states: ["MN", "RU", "KZ"]
	}
},
/*
  locations: {
    "0": {
      name: "Paris",
      lat: "48.866666670",
      lng: "2.333333333",
      color: "default",
      description: "default",
      url: "default"
    },
    "1": {
      name: "Tokyo",
      lat: "35.666666670",
      lng: "139.750000000",
      color: "default",
      description: "default",
      url: "default"
    },
    "3": {
      name: "New York",
      lat: "40.71",
      lng: "-74.0059731",
      description: "default",
      color: "default",
      url: "default",
      size: "default"
    }
  },
  */
  labels: {}
};