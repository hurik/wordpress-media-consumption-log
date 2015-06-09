/*
 Copyright (C) 2014-2015 Andreas Giemza <andreas@giemza.net>
 
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

google.load('visualization', '1.0', {
    'packages': ['corechart']
});

google.setOnLoadCallback(drawDailyChart);
google.setOnLoadCallback(drawMonthlyChart);

function drawDailyChart() {
    var daily_data_array = JSON.parse(js_params.daily);
    daily_data_array[0][daily_data_array[0].length - 1] = {role: 'annotation'};
    var data = google.visualization.arrayToDataTable(daily_data_array);

    var options = {
        annotations: {
            textStyle: {
                color: '#000000',
                fontSize: 9, bold: true
            },
            highContrast: true,
            alwaysOutside: true
        },
        height: data.getNumberOfRows() * 15 + 100,
        legend: {
            position: 'top',
            maxLines: 4,
            alignment: 'center'
        },
        bar: {
            groupWidth: '70%'
        },
        focusTarget: 'category',
        chartArea: {
            left: 70,
            top: 80,
            width: '80%',
            height: data.getNumberOfRows() * 15
        },
        isStacked: true
    };

    var chart = new google.visualization.BarChart(document.getElementById('daily_chart_div'));
    chart.draw(data, options);
}

function drawMonthlyChart() {
    var monthly_data_array = JSON.parse(js_params.monthly);
    monthly_data_array[0][monthly_data_array[0].length - 1] = {role: 'annotation'};
    var data = google.visualization.arrayToDataTable(monthly_data_array);

    var options = {
        annotations: {
            textStyle: {
                color: '#000000',
                fontSize: 9,
                bold: true
            },
            highContrast: true,
            alwaysOutside: true
        },
        height: data.getNumberOfRows() * 15 + 100,
        legend: {
            position: 'top',
            maxLines: 4,
            alignment: 'center'
        },
        bar: {
            groupWidth: '70%'
        },
        focusTarget: 'category',
        chartArea: {
            left: 50,
            top: 80,
            width: '80%',
            height: data.getNumberOfRows() * 15
        },
        isStacked: true
    };

    var chart = new google.visualization.BarChart(document.getElementById('monthly_chart_div'));
    chart.draw(data, options);
}

jQuery(document).ready(function ($) {
    $(window).resize(function () {
        drawDailyChart();
        drawMonthlyChart();
    });
});
