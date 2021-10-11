function getChartData(start, end) {
    //Get data by AJAX        
    var url = "<?= route("chart.read") ?>";
    $.ajax({
        method: "POST",
        url: url,
        data: {
            startDate: start,
            endDate: end
        },
        success: function (data) {
            //convert date
            let startDate = moment(start);
            let endDate = moment(end);
            $(".periode").text(startDate.format("DD/MM/YYYY") + " hingga " + endDate.format("DD/MM/YYYY"));
            $(".last-updated").text(moment().format("DD/MM/YYYY, h:mm:ss a"));
            updateChartData(lineChart, data);
        }
    });
}

function updateChartData(chart, data) {
    chart.data.labels = data.label;
    chart.data.datasets.forEach((dataset, index) => {
        dataset.data = data.emotion[index];
    });
    chart.update();
}

function getWordCloud() {
    var emotion = $("#emotion").val();
    var start = moment($("#date-range-word").data('daterangepicker').startDate._d).format('Y-MM-DD');
    var end = moment($("#date-range-word").data('daterangepicker').endDate._d).format('Y-MM-DD');
    var url = "<?= route('word.cloud') ?>";
    $.ajax({
        url: url,
        method: "POST",
        data: {
            emotion: emotion,
            startDate: start,
            endDate: end
        },
        dataType: "json",
        beforeSend: function () {
            $(".loader").toggleClass('d-none');
        },
        success: function (data) {
            $(".loader").toggleClass('d-none');
            WordCloud(document.getElementById('word-cloud'), {
                list: data
            });
        }
    });
}