//this will move selected items from source list to destination list   
function move_list_items(sourceid, destinationid)
  {
    jQuery("#"+sourceid+"  option:selected").appendTo("#"+destinationid);
  }

  //this will move all selected items from source list to destination list
  function move_list_items_all(sourceid, destinationid)
  {
    jQuery("#"+sourceid+" option").appendTo("#"+destinationid);
  }
