function loadPoolStation() 
{
    $.ajax({
        type: 'GET',
        //url: '../api/includes/ws_action.php',
        url: 'includes/ws_action.php',
        data: 'api=/pool/station',
        dataType: 'json',
        success: function (data) {
            //console.log(data);

            $.each(data, function (index, value) {
                //console.log(value);
                
                //value = value.toUpperCase()
                
                // ACTION START
                if (checkValue(value) != true) {
                    $('<option/>').attr('value',value).text(value).appendTo('#pool-station');
                }
                
                if ( document.getElementById("check_"+value) ) {
                    document.getElementById("check_"+value).checked = true;
                }
                
                // ACTION END
                
            });
        }
    });
}

function setPoolStation(value)
{
    $.ajax({
        type: 'GET',
        //url: '../api/includes/ws_action.php',
        url: 'includes/ws_action.php',
        data: 'api=/pool/station/'+value,
        dataType: 'json',
        success: function (data) {
            console.log(data);
        }
    });
}

function delPoolStation(value)
{
    $.ajax({
        type: 'GET',
        //url: '../api/includes/ws_action.php',
        url: 'includes/ws_action.php',
        data: 'api=/pool/station/'+value+"/del",
        dataType: 'json',
        success: function (data) {
            console.log(data);
        }
    });
}

function loadPoolSSID(args) {
    $.ajax({
        type: 'GET',
        //url: '../api/includes/ws_action.php',
        url: 'includes/ws_action.php',
        data: 'api=/pool/ssid',
        dataType: 'json',
        success: function (data) {
            //console.log(data);

            $.each(data, function (index, value) {
                //console.log(value);
                
                // ACTION START
                if (checkValue(value) != true) {
                    $('<option/>').attr('value',value).text(value).appendTo('#pool-ssid');
                }
                
                // ACTION END
                
            });
        }
    });
}

function setPoolSSID(value)
{
    $.ajax({
        type: 'GET',
        //url: '../api/includes/ws_action.php',
        url: 'includes/ws_action.php',
        data: 'api=/pool/ssid/'+value,
        dataType: 'json',
        success: function (data) {
            console.log(data);
        }
    });
}

function delPoolSSID(value)
{
    $.ajax({
        type: 'GET',
        //url: '../api/includes/ws_action.php',
        url: 'includes/ws_action.php',
        data: 'api=/pool/ssid/'+value+"/del",
        dataType: 'json',
        success: function (data) {
            console.log(data);
        }
    });
}

function addMAC2Select(value) {
    if (checkValue(value) != true && value != "") {
        $('<option/>').attr('value',value).text(value).appendTo('#pool-station');
    }
}

// REF: https://www.safaribooksonline.com/library/view/jquery-cookbook/9780596806941/ch10s07.html

function addListStation() {
//$('#add').click(function(event){
//    event.preventDefault();
    
    var value = $('#newMACText').val();
    
    if (checkValue(value) != true && value != "") {
        $('<option/>').attr('value',value).text(value).appendTo('#pool-station');
        setPoolStation(value);
    }
//});
}

function removeListStation() {
//$('#remove').click(function(event){
//    event.preventDefault();
    
    value = $('option:selected',$select).text();
    
    var $select = $('#pool-station');
    $('option:selected',$select).remove();
    
    delPoolStation(value);
//});
}

function addListSSID() {
//$('#add').click(function(event){
//    event.preventDefault();
    
    var value = $('#newSSIDText').val();
    
    if (checkValue(value) != true && value != "") {
        $('<option/>').attr('value',value).text(value).appendTo('#pool-ssid');
        setPoolSSID(value);
    }
//});
}

function removeListSSID() {
//$('#remove').click(function(event){
//    event.preventDefault();
    
    value = $('option:selected',$select).text();
    
    var $select = $('#pool-ssid');
    $('option:selected',$select).remove();
    
    delPoolSSID(value);
//});
}

function checkValue(MAC) {
    var exists = false; 
    $('#pool-station option').each(function(){
        //alert(this.text)
        //if (this.value == MAC) {
        if (this.text == MAC) {
            //alert(this.text)
            exists = true;
        }
    });
    return exists
}

function checkBox(data) {
    
    value = data.id.replace("check_", "")
    
    if (data.checked) {
        //addMAC2Select(value)
        if (checkValue(value) != true) {
            $('<option/>').attr('value',value).text(value).appendTo('#pool-station');
            setPoolStation(value);
        }
    } else {
        $("#pool-station option[value='"+value+"']").remove();
        delPoolStation(value);
    }
    
    //alert(data)
    //alert($('#'+data).attr('checked', true));
    //alert(data.checked)
}
/*
function switchStation() {
    value = $("#station-switch").val();
    if (value == "Allow") {
        $("#station-switch").val("Deny");
        setValue = "0";
    } else {
        $("#station-switch").val("Allow");
        setValue = "1";
    }
    $.getJSON('../api/includes/ws_action.php?api=/config/module/ap/mod_filter_station_mode/'+setValue, function(data) {});
}

function switchSSID() {
    value = $("#ssid-switch").val();
    if (value == "Whitelist") {
        $("#ssid-switch").val("Blacklist");
        setValue = "0";
    } else {
        $("#ssid-switch").val("Whitelist");
        setValue = "1";
    }
    $.getJSON('../api/includes/ws_action.php?api=/config/module/ap/mod_filter_ssid_mode/'+setValue, function(data) {});
}
*/
/*
function setFilter(item, param) {
    if (document.getElementById(item.id).checked) {
        //alert("on")
        value = "1";
    } else {
        //alert("off")
        value = "0";
    }
    $.getJSON('../api/includes/ws_action.php?api=/config/module/ap/'+param+'/'+value, function(data) {});
}
*/

function setCheckbox(item, param) {
    if (document.getElementById(item.id).checked) {
        //alert("on")
        value = "1";
    } else {
        //alert("off")
        value = "0";
    }
    $.getJSON('../api/includes/ws_action.php?api=/config/module/ap/'+param+'/'+value, function(data) {});
}

function setOption(item, param) {
	value = $("#"+item).val();
    $.getJSON('../api/includes/ws_action.php?api=/config/module/ap/'+param+'/'+value, function(data) {});
}

function loadStation()
{
	$.ajax({
		type: 'GET',
		//url: '../api/includes/ws_action.php',
        url: 'includes/ws_action.php',
		data: 'api=/scan/station',
		dataType: 'json',
		success: function (data) {
			console.log(data);
			//$('#output').html('');
			$.each(data, function (index, value) {
                console.log(value);
                console.log(value["station"])
				v_station = value["station"]
                v_inactive = value["inactive time"]
                v_signal = value["signal"]
                v_rxbytes = value["rx bytes"]
                v_txbytes = value["tx bytes"]
                //v_rxbytes = value[2]
                //v_txbytes = value[4]
                v_ip = value["ip"]
                v_hostname = value["hostname"]
				
				/*
                v_station = value[0][1]
                v_inactive = value[1][1]
                v_signal = value[8][1]
                v_rxbytes = value[2][1]
                v_txbytes = value[4][1]
                //v_rxbytes = value[2]
                //v_txbytes = value[4]
                v_ip = value[18][1] //19?
                v_hostname = value[19][1] //20?
                */
				
                content = "<div>"
                content = content + "<div class='divBSSID'>"+v_station+"</div>"
                content = content + "<div class='div1'>"+v_hostname+"</div>"
                content = content + "<div class='div1'>"+v_ip+"</div>"
                content = content + "<div class='div1'>"+v_inactive+"</div>"
                content = content + "<div class='div1'>"+v_signal+"</div>"
                content = content + "<div class='div1'>"+v_rxbytes+"</div>"
                content = content + "<div class='div1'>"+v_txbytes+"</div>"
                content = content + "</div>"
                $("#station").append(content)
				
			});
		}
	});

}

function cleanStation() {
    $('#station').html('');
}

//station()


// BLOCK 2
function loadingContent() {


    $('#formLogs').submit(function(event) {
        event.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'includes/ajax.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (data) {
                console.log(data);
    
                $('#output').html('');
                $.each(data, function (index, value) {
                    $("#output").append( value ).append("\n");
                });
                
                $('#loading').hide();
            }
        });
        
        $('#output').html('');
        $('#loading').show()
    
    });
    
    //$('#loading').hide();
    
    // BLOCK 3
    
    $('#form1').submit(function(event) {
        event.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'includes/ajax.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (data) {
                console.log(data);
    
                $('#output').html('');
                $.each(data, function (index, value) {
                    if (value != "") {
                        $("#output").append( value ).append("\n");
                    }
                });
                
                $('#loading').hide();
    
            }
        });
        
        $('#output').html('');
        $('#loading').show()
    
    });
    
    //$('#loading').hide();
    
    // BLOCK 4
    
    $('#formInject2').submit(function(event) {
        event.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'includes/ajax.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (data) {
                console.log(data);
    
                $('#inject').html('');
                $.each(data, function (index, value) {
                    $("#inject").append( value ).append("\n");
                });
                
                $('#loading').hide();
                
            }
        });
        
        $('#output').html('');
        $('#loading').show()
    
    });
    
    //$('#loading').hide();
}

function setOptionSelect(item, param) {
	var e = document.getElementById(item.id);
	var value = e.options[e.selectedIndex].text;
	
    $.getJSON('../api/includes/ws_action.php?api=/config/module/ap/'+param+'/'+value, function(data) {});
}