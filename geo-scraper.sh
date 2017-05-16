#! /bin/bash
#

while IFS='' read -r line || [[ -n "$line" ]]; do
       #echo "Text read from file: $line"

rawaddress=$line
token='' #your lojic token here
address=$(php -r "echo rawurlencode('$rawaddress');")

#echo $address

xydata=$(curl 'https://ags1.lojic.org/ArcGIS/rest/services/Metro/MapItQuery/MapServer/1/query?token=$token&f=json&where=PRIMARYSEARCH%20like%20%27'$address'%25%27&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=PRIMARYSEARCH%2CPOINT_X%2CPOINT_Y&callback=dojo.io.script.jsonp_dojoIoScript12._jsonpCallback' -H 'Pragma: no-cache' -H 'DNT: 1' -H 'Accept-Encoding: gzip, deflate, sdch, br' -H 'Accept-Language: en-US,en;q=0.8' -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36' -H 'Accept: */*' -H 'Referer: https://louisvilleky.gov/' -H 'Connection: keep-alive' -H 'Cache-Control: no-cache' --compressed)

#trims x number of characters in front : -x characters from back so the result is proper json
jsonresult=${xydata:51:-2} #results from lojic

#echo $jsonresult

# json properties below can be determined from network debug window in chrome. after a network activity has completed. choose the preview tab.
# then right click on the property you want to target and choose, 'copy property path'.
#example url from louisvilleky.gov - mylouisville address lookup tool :
#https://ags1.lojic.org//arcgis/rest/services/LOJIC/MetroServices/MapServer/exts/MetroServicesRestSoe/GetReport?token=wIVsENBdwhQJc2-n6AJNX_bHqE9aoraNHFLXJgMx4v8FaGR5xNdctCavqQHKmWSq06H2_K7Qd54Sj9XICA8u4A..&InputPoint=%7B%22x%22%3A1241280%2C%20%22y%22%3A263010%7D&f=json&callback=dojo.io.script.jsonp_dojoIoScript18._jsonpCallback


#echo $rawaddress
x=$(echo $jsonresult | jq -r ".features[0].attributes.POINT_X")
y=$(echo $jsonresult| jq -r ".features[0].attributes.POINT_Y")


servicedata=$(curl 'https://ags1.lojic.org//arcgis/rest/services/LOJIC/MetroServices/MapServer/exts/MetroServicesRestSoe/GetReport?token=wIVsENBdwhQJc2-n6AJNX_bHqE9aoraNHFLXJgMx4v8FaGR5xNdctCavqQHKmWSq06H2_K7Qd54Sj9XICA8u4A..&InputPoint=%7B%22x%22%3A'$x'%2C%20%22y%22%3A'$y'%7D&f=json&callback=dojo.io.script.jsonp_dojoIoScript9._jsonpCallback' -H 'Pragma: no-cache' -H 'DNT: 1' -H 'Accept-Encoding: gzip, deflate, sdch, br' -H 'Accept-Language: en-US,en;q=0.8' -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36' -H 'Accept: */*' -H 'Referer: https://louisvilleky.gov/' -H 'Connection: keep-alive' -H 'Cache-Control: no-cache' --compressed)

servicejsonresult=${servicedata:50:-2} #results from lojic

#echo $servicejsonresult
#.StreetSweeping.Routes["0"].AreaRoute

arearoute=$(echo $servicejsonresult | jq -r ".StreetSweeping.Routes[0].AreaRoute")

#echo $arearoute

output=$arearoute
#output=$(echo ''$rawaddress','$y)

target=output.csv

	echo "$output" >> "$target"

  done < "$1"
