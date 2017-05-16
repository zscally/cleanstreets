$(function(){

    if ( $( "#31daysdate" ).length ) {

        var data = null;
        $('#31daysdate').datetimepicker({
            format: "YYYY-MM-DD"
        });

        var date = null;
        $('#31daysdate').on('dp.change', function(){
            $('#over31days').html('');
            var date = $(this).val();
            getChartData(date);
            return false;
        });
        getChartData(date);

    }
});

function getChartData(date)
{
    date = (date ? date : '');
    doAjaxCall('get', '/admin/chartdata/' + date, 'json', {}, function (result) {
        if( result.subbydate.length > 0 ) {
            Morris.Bar({
                element: 'over31days',
                data: result.subbydate,
                xkey: 'date',
                ykeys: ['total_subscribers'],
                labels: ['Total Subscribers']
            });
        }else{
            $('#over31days').html('<div class="col-sm-12"><p class="text-center">No Chart Data</p></div>');
        }
    });
}
