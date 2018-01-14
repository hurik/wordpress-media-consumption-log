/*
 Copyright (C) 2014-2018 Andreas Giemza <andreas@giemza.net>
 
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

google.load( 'visualization', '1.0', {
    'packages': [ 'corechart' ]
} );

google.setOnLoadCallback( drawDailyChart );
google.setOnLoadCallback( drawMonthlyChart );
google.setOnLoadCallback( drawYearlyChart );
google.setOnLoadCallback( drawHourlyChart );
google.setOnLoadCallback( drawAverrageDevelopmentChart );

function drawDailyChart() {
    var daily_data_array = JSON.parse( js_params.daily );
    daily_data_array[0][daily_data_array[0].length - 1] = { role: 'annotation' };
    for ( var i = 1; i < daily_data_array.length; i++ ) {
        daily_data_array[i][0] = daily_data_array[i][0] + "";
    }
    var data = google.visualization.arrayToDataTable( daily_data_array );

    var options = {
        height: data.getNumberOfRows() * 15 + 100,
        width: '100%',
        annotations: {
            textStyle: {
                color: '#000000',
                fontSize: 9,
                bold: true
            },
            highContrast: true,
            alwaysOutside: true,
            stemColor: 'transparent',
            stemLength: 3
        },
        legend: {
            position: 'top',
            maxLines: 4,
            alignment: 'center'
        },
        bar: {
            groupWidth: '70%'
        },
        chartArea: {
            left: 70,
            top: 60,
            bottom: 20,
            right: 20,
            width: '100%',
            height: '100%'
        },
        focusTarget: 'category',
        isStacked: true
    };

    var series = { };
    series[data.getNumberOfColumns() - 3] = {
        color: 'transparent',
        type: "bar",
        targetAxisIndex: 1,
        visibleInLegend: false
    };

    options["series"] = series;

    var chart = new google.visualization.BarChart( document.getElementById( 'daily_chart_div' ) );
    chart.draw( data, options );
}

function drawMonthlyChart() {
    var monthly_data_array = JSON.parse( js_params.monthly );
    monthly_data_array[0][monthly_data_array[0].length - 1] = { role: 'annotation' };
    for ( var i = 1; i < monthly_data_array.length; i++ ) {
        monthly_data_array[i][0] = monthly_data_array[i][0] + "";
    }
    var data = google.visualization.arrayToDataTable( monthly_data_array );

    var options = {
        height: data.getNumberOfRows() * 15 + 100,
        width: '100%',
        annotations: {
            textStyle: {
                color: '#000000',
                fontSize: 9,
                bold: true
            },
            highContrast: true,
            alwaysOutside: true,
            stemColor: 'transparent',
            stemLength: 3
        },
        legend: {
            position: 'top',
            maxLines: 4,
            alignment: 'center'
        },
        bar: {
            groupWidth: '70%'
        },
        chartArea: {
            left: 70,
            top: 60,
            bottom: 20,
            right: 20,
            width: '100%',
            height: '100%'
        },
        focusTarget: 'category',
        isStacked: true
    };

    var series = { };
    series[data.getNumberOfColumns() - 3] = {
        color: 'transparent',
        type: "bar",
        targetAxisIndex: 1,
        visibleInLegend: false
    };

    options["series"] = series;


    var chart = new google.visualization.BarChart( document.getElementById( 'monthly_chart_div' ) );
    chart.draw( data, options );
}

function drawYearlyChart() {
    var yearly_data_array = JSON.parse( js_params.yearly );
    yearly_data_array[0][yearly_data_array[0].length - 1] = { role: 'annotation' };
    for ( var i = 1; i < yearly_data_array.length; i++ ) {
        yearly_data_array[i][0] = yearly_data_array[i][0] + "";
    }
    var data = google.visualization.arrayToDataTable( yearly_data_array );

    var options = {
        height: data.getNumberOfRows() * 15 + 100,
        width: '100%',
        annotations: {
            textStyle: {
                color: '#000000',
                fontSize: 9,
                bold: true
            },
            highContrast: true,
            alwaysOutside: true,
            stemColor: 'transparent',
            stemLength: 3
        },
        legend: {
            position: 'top',
            maxLines: 4,
            alignment: 'center'
        },
        bar: {
            groupWidth: '70%'
        },
        chartArea: {
            left: 70,
            top: 60,
            bottom: 20,
            right: 20,
            width: '100%',
            height: '100%'
        },
        focusTarget: 'category',
        isStacked: true
    };

    var series = { };
    series[data.getNumberOfColumns() - 3] = {
        color: 'transparent',
        type: "bar",
        targetAxisIndex: 1,
        visibleInLegend: false
    };

    options["series"] = series;


    var chart = new google.visualization.BarChart( document.getElementById( 'yearly_chart_div' ) );
    chart.draw( data, options );
}

function drawHourlyChart() {
    var hourly_data_array = JSON.parse( js_params.hourly );
    hourly_data_array[0][hourly_data_array[0].length - 1] = { role: 'annotation' };
    for ( var i = 1; i < hourly_data_array.length; i++ ) {
        hourly_data_array[i][0] = hourly_data_array[i][0] + "";
    }
    var data = google.visualization.arrayToDataTable( hourly_data_array );

    var options = {
        height: data.getNumberOfRows() * 15 + 100,
        width: '100%',
        annotations: {
            textStyle: {
                color: '#000000',
                fontSize: 9,
                bold: true
            },
            highContrast: true,
            alwaysOutside: true,
            stemColor: 'transparent',
            stemLength: 3
        },
        legend: {
            position: 'top',
            maxLines: 4,
            alignment: 'center'
        },
        bar: {
            groupWidth: '70%'
        },
        chartArea: {
            left: 70,
            top: 60,
            bottom: 20,
            right: 20,
            width: '100%',
            height: '100%'
        },
        focusTarget: 'category',
        isStacked: true
    };

    var series = { };
    series[data.getNumberOfColumns() - 3] = {
        color: 'transparent',
        type: "bar",
        targetAxisIndex: 1,
        visibleInLegend: false
    };

    options["series"] = series;


    var chart = new google.visualization.BarChart( document.getElementById( 'hourly_chart_div' ) );
    chart.draw( data, options );
}

function drawAverrageDevelopmentChart() {
    var averrage_data_array = JSON.parse( js_params.average );
    for ( var i = 1; i < averrage_data_array.length; i++ ) {
        averrage_data_array[i][0] = averrage_data_array[i][0] + "";
    }
    var data = google.visualization.arrayToDataTable( averrage_data_array );

    var options = {
        height: 400,
        legend: {
            position: 'top',
            maxLines: 4,
            alignment: 'center'
        },
        bar: {
            groupWidth: '100%'
        },
        focusTarget: 'category',
        chartArea: {
            left: 50,
            top: 80,
            width: '90%',
            height: 260,
        },
        isStacked: true,
        hAxis: {
            slantedText: true,
            slantedTextAngle: 45
        },
        lineWidth: 0.1,
        areaOpacity: 1
    };

    if ( parseFloat( js_params.average_max_delta ) > 0 ) {
        var average_max_delta = {
            viewWindow: {
                max: averrage_data_array[averrage_data_array.length - 1][averrage_data_array[averrage_data_array.length - 1].length - 1] + parseFloat( js_params.average_max_delta )
            }
        }

        options["vAxis"] = average_max_delta;
    }

    var series = { };
    series[data.getNumberOfColumns() - 2] = {
        color: 'transparent',
        targetAxisIndex: 1,
        pointsVisible: false,
        visibleInLegend: false
    };

    options["series"] = series;

    var chart = new google.visualization.AreaChart( document.getElementById( 'average_consumption_development_chart_div' ) );
    chart.draw( data, options );
}

jQuery( document ).ready( function ( $ ) {
    $( window ).resize( function () {
        drawDailyChart();
        drawMonthlyChart();
        drawYearlyChart();
        drawHourlyChart();
        drawAverrageDevelopmentChart();
    } );
} );
