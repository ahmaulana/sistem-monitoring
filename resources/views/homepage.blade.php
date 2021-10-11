@extends(backpack_view('blank'))

@section('before_styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .emotion-icon {
        max-width: 30% !important;
    }
</style>
@endsection
@section('content')
<div class="card-group mb-4">
    <div class="card">
        <div class="card-body">
            <div class="text-center">
                <img src="{{ asset('img/angry.png') }}" class="img-fluid emotion-icon" alt="">
                <div class="text-value anger">0</div><a href="{{ backpack_url('tweet') . '?label=anger' }}"><small class="text-muted text-uppercase font-weight-bold">Anger</small></a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="text-center">
                <img src="{{ asset('img/fear.png') }}" class="img-fluid emotion-icon" alt="">
                <div class="text-value fear">0</div><a href="{{ backpack_url('tweet') . '?label=fear' }}"><small class="text-muted text-uppercase font-weight-bold">Fear</small></a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="text-center">
                <img src="{{ asset('img/happy.png') }}" class="img-fluid emotion-icon" alt="">
                <div class="text-value happy">0</div><a href="{{ backpack_url('tweet') . '?label=happy' }}"><small class="text-muted text-uppercase font-weight-bold">Happy</small></a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="text-center">
                <img src="{{ asset('img/love.png') }}" class="img-fluid emotion-icon" alt="">
                <div class="text-value love">0</div><a href="{{ backpack_url('tweet') . '?label=love' }}"><small class="text-muted text-uppercase font-weight-bold">Love</small></a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="text-center">
                <img src="{{ asset('img/sad.png') }}" class="img-fluid emotion-icon" alt="">
                <div class="text-value sadness"></div><a href="{{ backpack_url('tweet') . '?label=sadness' }}"><small class="text-muted text-uppercase font-weight-bold">Sadness</small></a>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">Sistem Pengawasan Emosi Masyarakat</h4>
                <div class="small text-muted">Sumber Data: Twitter</div>
            </div>
            <!-- /.col-->
        </div>
        <!-- /.row-->
        <div class="chart-wrapper" style="height:300px;margin-top:40px;">
            <div class="chartjs-size-monitor">
                <div class="chartjs-size-monitor-expand">
                    <div class=""></div>
                </div>
                <div class="chartjs-size-monitor-shrink">
                    <div class=""></div>
                </div>
            </div>
            <canvas class="chart chartjs-render-monitor" id="main-chart" height="300" width="422" style="display: block; width: 422px; height: 300px;"></canvas>
            <div id="main-chart-tooltip" class="chartjs-tooltip center" style="opacity: 0; left: 265.616px; top: 210.14px;">
                <div class="tooltip-header">
                    <div class="tooltip-header-item">T</div>
                </div>
                <div class="tooltip-body">
                    <div class="tooltip-body-item"><span class="tooltip-body-item-color" style="background-color: rgb(70, 127, 208);"></span><span class="tooltip-body-item-label">My First dataset</span><span class="tooltip-body-item-value">161</span></div>
                    <div class="tooltip-body-item"><span class="tooltip-body-item-color" style="background-color: rgb(66, 186, 150);"></span><span class="tooltip-body-item-label">My Second dataset</span><span class="tooltip-body-item-value">84</span></div>
                    <div class="tooltip-body-item"><span class="tooltip-body-item-color" style="background-color: rgb(223, 71, 89);"></span><span class="tooltip-body-item-label">My Third dataset</span><span class="tooltip-body-item-value">65</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">Trend
        <div class="card-header-actions">
            <small class="text-muted">
                <div class="form-group col-sm-12" element="div">
                    <div class="input-group date">
                        <input type="text" class="form-control" id="date_range">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <span class="la la-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </small>
        </div>
    </div>
    <div class="card-body">
        <div class="chart-wrapper">
            <div class="chartjs-size-monitor">
                <div class="chartjs-size-monitor-expand">
                    <div class=""></div>
                </div>
                <div class="chartjs-size-monitor-shrink">
                    <div class=""></div>
                </div>
            </div>
            <canvas id="filter-chart" style="display: block; width: 269px; height: 134px;" width="269" height="134" class="chartjs-render-monitor"></canvas>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">Word Cloud
        <div class="card-header-actions">
            <small class="text-muted">
                <div class="input-group">
                    <select id="emotion" name="emotion" class="form-control">
                        <option value="anger">Anger</option>
                        <option value="happy">Happy</option>
                        <option value="fear">Fear</option>
                        <option value="love">Love</option>
                        <option value="sadness">Sadness</option>
                    </select>
                    <div class="input-group-append">
                        <button class="btn-update btn btn-sm btn-primary" type="button">update</button>
                    </div>
                </div>
            </small>
        </div>
    </div>
    <div class="card-body">
        <div class="chart-wrapper">
            <div id="word-cloud" class="w-100" style="height:300px"></div>
        </div>
    </div>
</div>
@endsection
@section('after_scripts')
<script src="/packages/wordcloud/wordcloud2.js"></script>
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function() {
        var total = <?= json_encode($total) ?>;
        countEmotion(total);

        //Initialize Date Range
        $("#date_range").daterangepicker({
            maxDate: new Date(),
            alwaysShowCalendars: true,
            locale: {
                format: 'DD/MM/YYYY'
            },

            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment().subtract(7, 'days'),
            endDate: moment()
        });

        //Filter Chart
        getChartData(moment().subtract(7, 'd').format('Y-MM-DD'), moment().format('Y-MM-DD'));

        //Get Word Cloud
        getWordCloud();

        //Update Word Cloud
        $(".btn-update").on("click", function() {
            getWordCloud();
        });
    });
    var label = <?= json_encode($emotions_chart['label']) ?>;
    var anger = <?= json_encode($emotions_chart['emotion']['anger']) ?>;
    var fear = <?= json_encode($emotions_chart['emotion']['fear']) ?>;
    var happy = <?= json_encode($emotions_chart['emotion']['happy']) ?>;
    var love = <?= json_encode($emotions_chart['emotion']['love']) ?>;
    var sadness = <?= json_encode($emotions_chart['emotion']['sadness']) ?>;
    var mainChart = new Chart($('#main-chart'), {
        type: 'line',
        data: {
            labels: label,
            datasets: [{
                label: 'Anger',
                backgroundColor: 'transparent',
                borderColor: 'red',
                pointHoverBackgroundColor: '#fff',
                borderWidth: 2,
                data: anger
            }, {
                label: 'Fear',
                backgroundColor: 'transparent',
                borderColor: 'grey',
                pointHoverBackgroundColor: '#fff',
                borderWidth: 2,
                data: fear
            }, {
                label: 'Happy',
                backgroundColor: 'transparent',
                borderColor: 'yellow',
                pointHoverBackgroundColor: '#fff',
                borderWidth: 2,
                data: happy
            }, {
                label: 'Love',
                backgroundColor: 'transparent',
                borderColor: 'pink',
                pointHoverBackgroundColor: '#fff',
                borderWidth: 2,
                data: love
            }, {
                label: 'Sadness',
                backgroundColor: 'transparent',
                borderColor: 'blue',
                pointHoverBackgroundColor: '#fff',
                borderWidth: 2,
                data: sadness
            }]
        },
        options: {
            maintainAspectRatio: false,
            legend: {
                display: false
            },
            scales: {
                xAxes: [{
                    type: 'time',
                    time: {
                        displayFormats: {
                            minute: 'h:mm a'
                        }
                    },
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        maxTicksLimit: 5,
                    }
                }]
            },
            elements: {
                point: {
                    radius: 2,
                    hitRadius: 10,
                    hoverRadius: 4,
                    hoverBorderWidth: 3
                }
            }
        }
    });

    var lineChart = new Chart($('#filter-chart'), {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Anger',
                backgroundColor: 'transparent',
                borderColor: 'red',
                pointHoverBackgroundColor: '#fff',
                borderWidth: 2,
                data: []
            }, {
                label: 'Fear',
                backgroundColor: 'transparent',
                borderColor: 'grey',
                pointHoverBackgroundColor: '#fff',
                borderWidth: 2,
                data: []
            }, {
                label: 'Happy',
                backgroundColor: 'transparent',
                borderColor: 'yellow',
                pointHoverBackgroundColor: '#fff',
                borderWidth: 2,
                data: []
            }, {
                label: 'Love',
                backgroundColor: 'transparent',
                borderColor: 'pink',
                pointHoverBackgroundColor: '#fff',
                borderWidth: 2,
                data: []
            }, {
                label: 'Sadness',
                backgroundColor: 'transparent',
                borderColor: 'blue',
                pointHoverBackgroundColor: '#fff',
                borderWidth: 2,
                data: []
            }]
        },
        options: {
            responsive: true,
            scales: {
                xAxes: [{
                    type: 'time',
                    time: {
                        unit: 'day'
                    },
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        maxTicksLimit: 5,
                    }
                }]
            },
        }
    });

    function addData(chart, label, data) {
        chart.data.labels.push(label);
        chart.data.labels.shift();
        chart.data.datasets.forEach((dataset, index) => {
            dataset.data.shift();
            dataset.data.push(data[index]);
        });
        chart.update();
    }

    function countEmotion(data) {
        $(".anger").html(data[0]['total']);
        $(".fear").html(data[1]['total']);
        $(".happy").html(data[2]['total']);
        $(".love").html(data[3]['total']);
        $(".sadness").html(data[4]['total']);
    }

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
            success: function(data) {
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
        var url = "<?= route('word.cloud') ?>";
        $.ajax({
            url: url,
            method: "POST",
            data: {
                emotion: emotion
            },
            dataType: "json",
            success: function(data) {
                WordCloud(document.getElementById('word-cloud'), {
                    list: data
                });
            }
        });
    }
</script>
<script>
    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var pusher = new Pusher('d6dc82355927033668cd', {
        cluster: 'ap1'
    });

    var channel = pusher.subscribe('chart-event');
    channel.bind('chart', function(data) {
        addData(mainChart, data.message.label, data.message.emotion);
        countEmotion(data.message.total);
    });
    $(document).ready(function() {
        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            var start = moment(picker.startDate).format('Y-MM-DD');
            var end = moment(picker.endDate).format('Y-MM-DD');
            getChartData(start, end);
        });
    });
</script>
@endsection