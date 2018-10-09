jQuery(document).ready(function() {
    jQuery(".osm-map").each(function() {
        var map = jQuery(this),
            lat = map.attr("data-lat"),
            lon = map.attr("data-lon"),
            zoom = map.attr("data-zoom"),
            id = map.attr("id"),
            js;
        
        if (id === undefined) {
            id = "map" + Math.floor((1 + Math.random()) * 0x10000000).toString(16).substring(1);
            map.attr("id", id);
        }

        js = "{\n"
           + "var map=new L.Map('" + id + "', osmAKuechlerConfig.mapConfig);\n"
           + "map.attributionControl.setPrefix('');\n"
           + "var baselayer=new L.TileLayer(osmAKuechlerConfig.server, osmAKuechlerConfig.tileConfig);\n"
           + "var koord=new L.LatLng(" + lat + ", " + lon + ");\n"
           + "map.setView(koord, " + zoom + ").addLayer(baselayer);\n"
           + "\n";

        map.children(".osm-point").each(function() {
            var point = jQuery(this),
                lat = point.attr("data-lat"),
                lon = point.attr("data-lon"),        
                data = point.html();

            js += "{\n"
               + "var koord=new L.LatLng(" + lat + ", " + lon + ");\n"
               + "var marker=new L.Marker(koord);\n"
               + "map.addLayer(marker);\n"
               + "marker.bindPopup('" + data.replace(/[\n\r]+/g, "") + "');\n"
               + "}\n";

            point.remove();
        });
        js += "}\n";
        
        map.append("<script>" + js + "</script>");
    });
});

