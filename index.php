<!DOCTYPE html>
<?php
//Just a very simple test page, testing the different kinds of requests and data types being sent.
$endpoint = '/api';
$methods = array(
    array(
        'type' => 'get',
        'data' => array(
            'name' => 'John Doe',
            'company' => 'Yellow Industries, Inc.',
            'email' => 'jdoe@yellowinc.com',
            'phone' => '(925) 123-4567'
        )
    ),
    array(
        'type' => 'delete',
        'data' => array(
            'name' => 'John Doe',
            'company' => 'Yellow Industries, Inc.',
            'email' => 'jdoe@yellowinc.com',
            'phone' => '(925) 123-4567'
        )
    ),
    array(
        'type' => 'post',
        'data' => array(
            'name' => 'John Doe',
            'company' => 'Yellow Industries, Inc.',
            'email' => 'jdoe@yellowinc.com',
            'phone' => '(925) 123-4567'
        )
    ),
    array(
        'type' => 'put',
        'data' => array(
            'name' => 'John Doe',
            'company' => 'Yellow Industries, Inc.',
            'email' => 'jdoe@yellowinc.com',
            'phone' => '(925) 123-4567'
        )
    )
);
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>REST Server Test Page</title>
        <style>
            form {
                padding: 20px 50px;
                border: 1px solid black;
            }
            button {
                background-color: white;
                border: 1px solid black;
                border-radius: 0;
                padding: 15px;
                cursor: pointer;
                display: inline-block;
                margin-left: 20px;
            }
            form button:first-child {
                margin-left: 0;
            }
            button:hover {
                background-color: #666666;
                color: #ffffff;
            }
        </style>
    </head>
    <body>
        <?php foreach($methods as $method){ ?>
            <form action="<?php echo $endpoint; ?>" method="<?php echo $method['type']; ?>">
                <?php foreach($method['data'] as $field_name => $field_data){ ?>
                    <input type="hidden" name="<?php echo $field_name; ?>" value="<?php echo $field_data; ?>" />
                <?php } ?>
                <button class="sub" data-type="form" type="button">TEST <?php echo strtoupper($method['type']); ?> (formstring)</button>
                <button class="sub" data-type="json" type="button">TEST <?php echo strtoupper($method['type']); ?> (json)</button>
                <button class="sub" data-type="xml" type="button">TEST <?php echo strtoupper($method['type']); ?> (xml)</button>
                <button class="sub" data-type="csv" type="button">TEST <?php echo strtoupper($method['type']); ?> (csv)</button>
                <button class="sub" data-type="serialized" type="button">TEST <?php echo strtoupper($method['type']); ?> (serialized)</button>
            </form>
        <?php } ?>
        <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
        <script type="text/javascript">
            function serialize(mixed_value) {
                //  discuss at: http://phpjs.org/functions/serialize/
                // original by: Arpad Ray (mailto:arpad@php.net)
                // improved by: Dino
                // improved by: Le Torbi (http://www.letorbi.de/)
                // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net/)
                // bugfixed by: Andrej Pavlovic
                // bugfixed by: Garagoth
                // bugfixed by: Russell Walker (http://www.nbill.co.uk/)
                // bugfixed by: Jamie Beck (http://www.terabit.ca/)
                // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net/)
                // bugfixed by: Ben (http://benblume.co.uk/)
                //    input by: DtTvB (http://dt.in.th/2008-09-16.string-length-in-bytes.html)
                //    input by: Martin (http://www.erlenwiese.de/)
                //        note: We feel the main purpose of this function should be to ease the transport of data between php & js
                //        note: Aiming for PHP-compatibility, we have to translate objects to arrays
                //   example 1: serialize(['Kevin', 'van', 'Zonneveld']);
                //   returns 1: 'a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}'
                //   example 2: serialize({firstName: 'Kevin', midName: 'van', surName: 'Zonneveld'});
                //   returns 2: 'a:3:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";s:7:"surName";s:9:"Zonneveld";}'

                var val, key, okey,
                  ktype = '',
                  vals = '',
                  count = 0,
                  _utf8Size = function(str) {
                    var size = 0,
                      i = 0,
                      l = str.length,
                      code = '';
                    for (i = 0; i < l; i++) {
                      code = str.charCodeAt(i);
                      if (code < 0x0080) {
                        size += 1;
                      } else if (code < 0x0800) {
                        size += 2;
                      } else {
                        size += 3;
                      }
                    }
                    return size;
                  };
                _getType = function(inp) {
                  var match, key, cons, types, type = typeof inp;

                  if (type === 'object' && !inp) {
                    return 'null';
                  }
                  if (type === 'object') {
                    if (!inp.constructor) {
                      return 'object';
                    }
                    cons = inp.constructor.toString();
                    match = cons.match(/(\w+)\(/);
                    if (match) {
                      cons = match[1].toLowerCase();
                    }
                    types = ['boolean', 'number', 'string', 'array'];
                    for (key in types) {
                      if (cons == types[key]) {
                        type = types[key];
                        break;
                      }
                    }
                  }
                  return type;
                };
                type = _getType(mixed_value);

                switch (type) {
                    case 'function':
                        val = '';
                        break;
                    case 'boolean':
                        val = 'b:' + (mixed_value ? '1' : '0');
                        break;
                    case 'number':
                        val = (Math.round(mixed_value) == mixed_value ? 'i' : 'd') + ':' + mixed_value;
                        break;
                    case 'string':
                        val = 's:' + _utf8Size(mixed_value) + ':"' + mixed_value + '"';
                        break;
                    case 'array':
                    case 'object':
                        val = 'a';
                        /*
                         if (type === 'object') {
                         var objname = mixed_value.constructor.toString().match(/(\w+)\(\)/);
                         if (objname == undefined) {
                         return;
                         }
                         objname[1] = this.serialize(objname[1]);
                         val = 'O' + objname[1].substring(1, objname[1].length - 1);
                         }
                         */

                        for (key in mixed_value) {
                            if (mixed_value.hasOwnProperty(key)) {
                                ktype = _getType(mixed_value[key]);
                                if (ktype === 'function') {
                                    continue;
                                }

                                okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key);
                                vals += this.serialize(okey) + this.serialize(mixed_value[key]);
                                count++;
                            }
                        }
                        val += ':' + count + ':{' + vals + '}';
                        break;
                    case 'undefined':
                        // Fall-through
                    default:
                        // if the JS object has a property which contains a null value, the string cannot be unserialized by PHP
                        val = 'N';
                        break;
                }
                if (type !== 'object' && type !== 'array') {
                    val += ';';
                }
                return val;
            }
        </script>
        <script type="text/javascript">
            /*	This work is licensed under Creative Commons GNU LGPL License.

                Source: http://goessner.net/download/prj/jsonxml/json2xml.js
                License: http://creativecommons.org/licenses/LGPL/2.1/
                Version: 0.9
                Author:  Stefan Goessner/2006
                Web:     http://goessner.net/ 
            */
            function json2xml(o, tab) {
               var toXml = function(v, name, ind) {
                  var xml = "";
                  if (v instanceof Array) {
                     for (var i=0, n=v.length; i<n; i++)
                        xml += ind + toXml(v[i], name, ind+"\t") + "\n";
                  }
                  else if (typeof(v) == "object") {
                     var hasChild = false;
                     xml += ind + "<" + name;
                     for (var m in v) {
                        if (m.charAt(0) == "@")
                           xml += " " + m.substr(1) + "=\"" + v[m].toString() + "\"";
                        else
                           hasChild = true;
                     }
                     xml += hasChild ? ">" : "/>";
                     if (hasChild) {
                        for (var m in v) {
                           if (m == "#text")
                              xml += v[m];
                           else if (m == "#cdata")
                              xml += "<![CDATA[" + v[m] + "]]>";
                           else if (m.charAt(0) != "@")
                              xml += toXml(v[m], m, ind+"\t");
                        }
                        xml += (xml.charAt(xml.length-1)=="\n"?ind:"") + "</" + name + ">";
                     }
                  }
                  else {
                     xml += ind + "<" + name + ">" + v.toString() +  "</" + name + ">";
                  }
                  return xml;
               }, xml="";
               for (var m in o)
                  xml += toXml(o[m], m, "");
               return tab ? xml.replace(/\t/g, tab) : xml.replace(/\t|\n/g, "");
            }
        </script>
        <script type="text/javascript">
            /*	
             * Source: http://jsfiddle.net/sturtevant/vunf9/
            */
            function JSON2CSV(objArray) {
                var array = typeof objArray !== 'object' ? JSON.parse(objArray) : objArray;

                var str = '';
                var line = '';
                var head = array[0];
                for (var index in array[0]) {
                    var value = index + "";
                    line += '"' + value.replace(/"/g, '""') + '",';
                }
                line = line.slice(0, -1);
                str += line + '\r\n';
                for (var i = 0; i < array.length; i++) {
                    var line = '';
                    for (var index in array[i]) {
                        var value = array[i][index] + "";
                        line += '"' + value.replace(/"/g, '""') + '",';
                    }
                    line = line.slice(0, -1);
                    str += line + '\r\n';
                }
                return str;

            }
        </script>
        <script type="text/javascript">
            var types = {
                form: 'application/x-www-form-urlencoded'
            ,   json: 'application/json'
            ,   xml: 'application/xml'
            ,   csv: 'application/csv'
            ,   serialized: 'application/vnd.php.serialized'
            };
            $(document).ready(function(){
                $('form .sub').on('click', function(){
                    var form = $(this).parent('form'), data, type;
                    if(types.hasOwnProperty(this.dataset['type'])){
                        type = types[this.dataset['type']];
                        switch(this.dataset['type']){
                            case 'form':{
                                data = form.serialize();
                                break;
                            }
                            case 'json':{
                                var t = form.serializeArray(), f = {};
                                for(var i in t){
                                    f[t[i].name] = t[i].value;
                                }
                                data = JSON.stringify(f);
                                break;
                            }
                            case 'xml':{
                                var t = form.serializeArray(), f = {};
                                for(var i in t){
                                    f[t[i].name] = t[i].value;
                                }
                                data = "<data>"+json2xml(f)+"</data>";
                                break;
                            }
                            case 'csv':{
                                var t = form.serializeArray(), f = [], cur_obj = {};
                                for(var i in t){
                                    cur_obj[t[i].name] = t[i].value;
                                }
                                f.push(cur_obj);
                                data = JSON2CSV(f);
                                break;
                            }
                            case 'serialized':{
                                var t = form.serializeArray(), f = {};
                                for(var i in t){
                                    f[t[i].name] = t[i].value;
                                }
                                data = serialize(f);
                                break;
                            }
                        }
                    } else {
                        console.warn('Invalid content-type');
                        return;
                    }
                    $.ajax(form.attr('action'),{
                        data: data,
                        method: form.attr('method').toUpperCase(),
                        dataType: 'json',
                        beforeSend: function(xhr){
                            xhr.setRequestHeader("Content-Type", type);
                            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                        },
                        complete: function(data){
                            data = data.responseText;
                            var data_type = Object.prototype.toString.call(data).replace(/\[object (.*)\]/, '$1').toLowerCase();
                            if(data_type !== 'string'){
                                data = JSON.stringify(data);
                            }
                            form.append('<hr/>'+data);
                        }
                    });
                });
            });
        </script>
    </body>
</html>
