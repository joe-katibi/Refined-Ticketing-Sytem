@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashbaord')
@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css')}}">
<style>.table {
  margin: 0;
  padding: 0;
}

.table-sm tbody tr td,
.table-sm thead tr th {
  padding: 0.4rem;
}

.nav-pills .nav-link {
  font-size: 0.9rem;
  padding: 0.5rem 1rem;
}

.card-header,
.card-body {
  padding: 0.5rem 1rem;
}

</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
<script>
  var monthlyData = {!! json_encode($monthlyData) !!};
  var monthlyLabels = {!! json_encode($monthlyLabels) !!};

  var totalMonthlyDataSum = monthlyData.reduce((a, b) => a + b, 0);

  var options = {
      series: [{
          name: 'Monthly Audit Results',
          data: monthlyData
      }],
      chart: {
          type: 'bar',
          height: 350,
          background: '#1E1E1E' // Optional: Set a dark background for the chart
      },
      plotOptions: {
          bar: {
              horizontal: false,
          }
      },
      dataLabels: {
          enabled: true,
          formatter: function (value) {
            return value + '%'; 
          },
          style: {
              fontSize: '14px',
              colors: ['#FFFFFF'] // Light color for data labels
          }
      },
      xaxis: {
          categories: monthlyLabels,
          labels: {
              rotate: -90,
              rotateAlways: true,
              style: {
                  colors: '#FFFFFF', // Set X-axis label color to white
                  fontSize: '12px'
              }
          },
          axisBorder: {
              color: '#FFFFFF' // Set X-axis border color to white
          },
          axisTicks: {
              color: '#FFFFFF' // Set X-axis tick color to white
          }
      },
      yaxis: {
          title: {
              text: 'Values (%)',
              style: {
                  color: '#FFFFFF' // Set Y-axis title color to white
              }
          },
          labels: {
              style: {
                  colors: '#FFFFFF', // Set Y-axis label color to white
                  fontSize: '12px'
              },
              formatter: function (value) {
                  return value + '%';
              }
          },
          axisBorder: {
              color: '#FFFFFF' // Set Y-axis border color to white
          },
          axisTicks: {
              color: '#FFFFFF' // Set Y-axis tick color to white
          }
      },
      tooltip: {
          y: {
              formatter: function (value) {
                  return value  + '%';
              }
          }
      },
      title: {
          text: 'Monthly Audit Results',
          align: 'center',
          style: {
              fontSize: '20px',
              color: '#EDA60DFF'
          }
      },
      legend: {
          position: 'top',
          horizontalAlign: 'center',
          floating: false,
          fontSize: '14px',
          labels: {
              colors: '#FFFFFF', // Set legend label color to white
              useSeriesColors: false
          }
      }
  };

  var chart = new ApexCharts(document.querySelector("#audit-chart"), options);
  chart.render();
</script>
<script>
  var percentages = {!! json_encode($percentages) !!}; // Replacing monthlyData with percentages
  var monthlyLabels = {!! json_encode($monthlyLabels) !!};

  var options = {
      series: [{
          name: 'Monthly Exam Percentage',
          data: percentages // Use percentages for the graph data
      }],
      chart: {
          type: 'bar',
          height: 350,
          background: '#1E1E1E' // Optional: Set a dark background for the chart
      },
      plotOptions: {
          bar: {
              horizontal: false,
          }
      },
      dataLabels: {
          enabled: true,
          formatter: function (value) {
              return value + '%'; // Show percentage values
          },
          style: {
              fontSize: '14px',
              colors: ['#FFFFFF'] // Light color for data labels
          }
      },
      xaxis: {
          categories: monthlyLabels, // Keep monthlyLabels for the X-axis
          labels: {
              rotate: -90,
              rotateAlways: true,
              style: {
                  colors: '#FFFFFF', // Set X-axis label color to white
                  fontSize: '12px'
              }
          },
          axisBorder: {
              color: '#FFFFFF' // Set X-axis border color to white
          },
          axisTicks: {
              color: '#FFFFFF' // Set X-axis tick color to white
          }
      },
      yaxis: {
          title: {
              text: 'Percentage (%)',
              style: {
                  color: '#FFFFFF' // Set Y-axis title color to white
              }
          },
          labels: {
              style: {
                  colors: '#FFFFFF', // Set Y-axis label color to white
                  fontSize: '12px'
              },
              formatter: function (value) {
                  return value + '%'; // Display values as percentages
              }
          },
          axisBorder: {
              color: '#FFFFFF' // Set Y-axis border color to white
          },
          axisTicks: {
              color: '#FFFFFF' // Set Y-axis tick color to white
          }
      },
      tooltip: {
          y: {
              formatter: function (value) {
                  return value + '%'; // Show percentage in the tooltip
              }
          }
      },
      title: {
          text: 'Monthly Exam Percentage',
          align: 'center',
          style: {
              fontSize: '20px',
              color: '#EDA60DFF'
          }
      },
      legend: {
          position: 'top',
          horizontalAlign: 'center',
          floating: false,
          fontSize: '14px',
          labels: {
              colors: '#FFFFFF', // Set legend label color to white
              useSeriesColors: false
          }
      }
  };

  var chart = new ApexCharts(document.querySelector("#exam-chart"), options);
  chart.render();
</script>
<script>
  
  // Store all monthly data
  const monthlyDataClassification = @json($monthlyDataClassification);
  
  // Function to update chart
  function updateChart(month) {
      const data = monthlyDataClassification[month] || [];
      const series = data.map(item => item.percentage);
      const labels = data.map(item => item.name);

      const options = {
          series: series,
          chart: {
              type: 'pie',
              height: 380
          },
          labels: labels,
          responsive: [{
              breakpoint: 480,
              options: {
                  chart: {
                      width: 320
                  },
                  legend: {
                      position: 'bottom'
                  }
              }
          }],
          tooltip: {
              y: {
                  formatter: function(value) {
                      return value + '%';
                  }
              }
          },
          legend: {
              position: 'right',
              horizontalAlign: 'center',
              floating: false,
              fontSize: '14px',
              labels: {
                  colors: ['#EDA60DFF'], // Set color of the legend labels
                  useSeriesColors: false // Disable using series color for legend
              }
          }
      };

      // Clear previous chart if it exists
      document.querySelector("#classificationChart").innerHTML = '';
      
      // Create new chart
      const chart = new ApexCharts(document.querySelector("#classificationChart"), options);
      chart.render();
  }

  // Initialize chart with current month
  document.addEventListener('DOMContentLoaded', function() {
      updateChart('{{ $currentMonth }}');

      // Add event listener for month selection
      document.getElementById('monthSelector').addEventListener('change', function () {
        const selectedMonth = this.value;
    updateChart(selectedMonth);
    });

  });
</script>
<script>
  // Store all monthly data
  const monthlyDataDisposition = @json($monthlyDataDisposition);
  // Function to update chart
  function updateChartDisposition(month) {
      const data = monthlyDataDisposition[month] || [];
      const series = data.map(item => item.percentage);
      const labels = data.map(item => item.name);
      const options = {
          series: series,
          chart: {
              type: 'pie',
              height: 380
          },
          labels: labels,
          responsive: [{
              breakpoint: 480,
              options: {
                  chart: {
                      width: 320
                  },
                  legend: {
                      position: 'bottom'
                  }
              }
          }],
          tooltip: {
              y: {
                  formatter: function(value) {
                      return value + '%';
                  }
              }
          },
          legend: {
              position: 'right',
              horizontalAlign: 'center',
              floating: false,
              fontSize: '14px',
              labels: {
                  colors: ['#EDA60DFF'], // Set color of the legend labels
                  useSeriesColors: false // Disable using series color for legend
              }
          }
      };

      // Clear previous chart if it exists
      document.querySelector("#dispositionChart").innerHTML = '';
      
      // Create new chart
      const chart = new ApexCharts(document.querySelector("#dispositionChart"), options);
      chart.render();
  }

  // Initialize chart with current month
  document.addEventListener('DOMContentLoaded', function() {
    updateChartDisposition('{{ $currentMonthDisposition }}');

      // Add event listener for month selection
      document.getElementById('monthSelectorDisposition').addEventListener('change', function () {
        const selectedMonth = this.value;
        updateChartDisposition(selectedMonth);
  });

  });
</script>


@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row g-2">
    <!-- Statistics -->
    <div class="col-xxl-6 col-md-4 mb-2">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <h5 class="card-title mb-0">Audits</h5>
        </div>
        <div class="card-body d-flex align-items-end">
          <div class="w-100">
            <div class="row gy-3">
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-info me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $auditTotal }}</h5>
                    <small>Total</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-success me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $auditCompleted }}</h5>
                    <small>Complete</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-warning me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $auditPending }}</h5>
                    <small>Pending</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-danger me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $auditSlipping }}</h5>
                    <small>slipping</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--/ Statistics -->
     <div class="col-xl-6 col-md-4 mb-2">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <h5 class="card-title mb-0">Exams</h5>
        </div>
        <div class="card-body d-flex align-items-end">
          <div class="w-100">
            <div class="row gy-3">
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-info me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $examTotalDone }}</h5>
                    <small>Completed</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-success me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $courseTotal }}</h5>
                    <small>Courses</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-warning me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $totalExams }}</h5>
                    <small>Exams</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-danger me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $totalQuestions }}</h5>
                    <small>Questions</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row g-2">
    <!-- Statistics -->
    <div class="col-xxl-6 col-md-4 mb-2">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <h5 class="card-title mb-0">Coaching</h5>
        </div>
        <div class="card-body d-flex align-items-end">
          <div class="w-100">
            <div class="row gy-3">
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-info me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $coachingTotal }}</h5>
                    <small>Total</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-success me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $coachingCompleted }}</h5>
                    <small>Complete</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-warning me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $coachingPending }}</h5>
                    <small>Pending</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-danger me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $coachingSlipping }}</h5>
                    <small>slipping</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--/ Statistics -->
     <div class="col-xl-6 col-md-4 mb-2">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <h5 class="card-title mb-0">Auto Fails</h5>
        </div>
        <div class="card-body d-flex align-items-end">
          <div class="w-100">
            <div class="row gy-3">
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-info me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $autoTotal }}</h5>
                    <small>Total</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-success me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $autoCompleted }}</h5>
                    <small>Complete</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-warning me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $autoPending }}</h5>
                    <small>Pending</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded bg-label-danger me-4 p-2"><i class="ti ti-credit-card ti-28px"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $autoSlipping }}</h5>
                    <small>slipping</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

    <!--/ Statistics -->
  <div class="row g-2">

    <div class="col-xxl-6 col-md-4 mb-2">
      <div class="card h-100">
        <div class="card-header" style="margin-bottom: 0; padding-bottom: 0;">
          <div>
            <h5 class="mb-1">Audit Summary</h5>
          </div>

          <ul class="nav nav-pills nav-fill" role="tablist">
            @foreach($data->unique('service_id') as $service)
            <li class="nav-item" role="presentation">
              <a href="#tab-service-{{ $service['service_id'] }}"
                 class="nav-link btn bg-warning btn-xs {{ $loop->first ? 'active' : '' }}"
                 data-bs-toggle="tab"
                 role="tab"
                 aria-controls="tab-service-{{ $service['service_id'] }}"
                 aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                {{ $service['service_name'] }}
              </a>
            </li>
            @endforeach
          </ul>
        </div>
        <div class="card-body" style="margin-bottom: 0; padding-bottom: 0;">
          <div class="tab-content ">
            @foreach($data->unique('service_id') as $service)
            <div class="tab-pane fade  {{ $loop->first ? 'show active' : '' }}"
                 id="tab-service-{{ $service['service_id'] }}"
                 role="tabpanel">
              <ul class="nav nav-pills widget-nav-tabs pb-2 gap-2 mx-1 d-flex flex-nowrap" role="tablist">
                @foreach($data->where('service_id', $service['service_id'])->unique('country_id') as $country)
                <li class="nav-item">
                  <a href="#tab-country-{{ $service['service_id'] }}-{{ $country['country_id'] }}"
                     class="nav-link btn bg-warning btn-xs{{ $loop->first ? 'active' : '' }}"
                     data-bs-toggle="tab">
                    {{ $country['country_name'] }}
                  </a>
                </li>
                @endforeach
              </ul>
              <div class="tab-content" style="margin-bottom: 0; padding-bottom: 0;">
                @foreach($data->where('service_id', $service['service_id'])->unique('country_id') as $country)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                     id="tab-country-{{ $service['service_id'] }}-{{ $country['country_id'] }}"
                     role="tabpanel">
                  <div class="table-responsive">
                    <table class="table table-sm">
                      <thead>
                        <tr>
                          <th class="text-start">Category</th>
                          <th class="text-center">{{ $currentMonthNames[0] }}{{ $monthYears[0] % 100 }}</th>
                          <th class="text-center">{{ $currentMonthNames[1] }}{{ $monthYears[1] % 100 }}</th>
                          <th class="text-center">{{ $currentMonthNames[2] }}{{ $monthYears[2] % 100 }}</th>
                          <th class="text-center">{{ $currentMonthNames[3] }}{{ $monthYears[3] % 100 }}</th>
                          <th class="text-center">{{ $currentYear % 100 }}WK{{ $currentWeek }}</th>
                      </tr>
                      </thead>
                      <tbody>
                        @foreach($data->where('service_id', $service['service_id'])->where('country_id', $country['country_id']) as $category)
                        <tr>
                            <td class="text-start">{{ $category['category_name'] }}</td>
                            <td class="text-center
                            @if($category['average_3rd_month'] >= 98)
                                text-success
                            @elseif($category['average_3rd_month'] >= 88 && $category['average_3rd_month'] < 98)
                                text-warning
                            @else
                                text-danger
                            @endif
                        ">
                            {{ number_format($category['average_3rd_month'], 0) }}%
                        </td>

                          <td class="text-center
                          @if($category['average_2nd_month'] >= 98)
                              text-success
                          @elseif($category['average_2nd_month'] >= 88 && $category['average_2nd_month'] < 98)
                              text-warning
                          @else
                              text-danger
                          @endif
                      ">
                          {{ number_format($category['average_2nd_month'], 0) }}%
                      </td>

                        <td class="text-center
                        @if($category['average_1st_month'] >= 98)
                            text-success
                        @elseif($category['average_1st_month'] >= 88 && $category['average_1st_month'] < 98)
                            text-warning
                        @else
                            text-danger
                        @endif
                    ">
                        {{ number_format($category['average_1st_month'], 0) }}%
                    </td>

                      <td class="text-center
                      @if($category['average_current_month'] >= 98)
                          text-success
                      @elseif($category['average_current_month'] >= 88 && $category['average_current_month'] < 98)
                          text-warning
                      @else
                          text-danger
                      @endif
                  ">
                      {{ number_format($category['average_current_month'], 0) }}%
                  </td>
                    <td class="text-center
                    @if($category['average_current_week'] >= 98)
                        text-success
                    @elseif($category['average_current_week'] >= 88 && $category['average_current_week'] < 98)
                        text-warning
                    @else
                        text-danger
                    @endif
                ">
                    {{ number_format($category['average_current_week'], 0) }}%
                </td>

                        </tr>
                    @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <!---->
    <div class="col-xxl-6 col-md-4 mb-2">
      <div class="card h-100">
          <div class="card-header" style="margin-bottom: 0; padding-bottom: 0;">
              <h5 class="mb-1">Exam Summary</h5>
              <ul class="nav nav-pills nav-fill" role="tablist">
                  @foreach($categoryExamQuery->unique('service_id') as $service)
                      <li class="nav-item" role="presentation">
                          <a href="#tab-service-{{ $service['service_id'] }}"
                             class="nav-link btn btn-xs bg-warning {{ $loop->first ? 'active' : '' }}"
                             data-bs-toggle="tab"
                             role="tab">
                              {{ $service['service_name'] }}
                          </a>
                      </li>
                  @endforeach
              </ul>
          </div>
          <div class="card-body" style="margin-bottom: 0; padding-bottom: 0;">
              <div class="tab-content">
                  @foreach($categoryExamQuery->unique('service_id') as $service)
                      <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                           id="tab-service-{{ $service['service_id'] }}"
                           role="tabpanel">
                          <ul class="nav nav-pills widget-nav-tabs pb-2 gap-2 mx-1 d-flex flex-nowrap" role="tablist">
                              @foreach($categoryExamQuery->where('service_id', $service['service_id'])->unique('country_id') as $country)
                                  <li class="nav-item">
                                      <a href="#tab-country-{{ $service['service_id'] }}-{{ $country['country_id'] }}"
                                         class="nav-link btn bg-warning btn-xs {{ $loop->first ? 'active' : '' }}"
                                         data-bs-toggle="tab">
                                          {{ $country['country_name'] }}
                                      </a>
                                  </li>
                              @endforeach
                          </ul>
                          <div class="tab-content">
                              @foreach($categoryExamQuery->where('service_id', $service['service_id'])->unique('country_id') as $country)
                                  <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                       id="tab-country-{{ $service['service_id'] }}-{{ $country['country_id'] }}"
                                       role="tabpanel">
                                      <div class="table-responsive">
                                          <table class="table table-sm">
                                              <thead>
                                                <tr>
                                                  <th class="text-start">Category</th>
                                                  <th class="text-center">{{ $currentMonthNames[0] }}{{ $monthYears[0] % 100 }}</th>
                                                  <th class="text-center">{{ $currentMonthNames[1] }}{{ $monthYears[1] % 100 }}</th>
                                                  <th class="text-center">{{ $currentMonthNames[2] }}{{ $monthYears[2] % 100 }}</th>
                                                  <th class="text-center">{{ $currentMonthNames[3] }}{{ $monthYears[3] % 100 }}</th>
                                                  <th class="text-center">{{ $currentYear % 100 }}WK{{ $currentWeek }}</th>
                                              </tr>
                                              </thead>
                                              <tbody>
                                                @foreach($categoryExamQuery->where('service_id', $service['service_id'])->where('country_id', $country['country_id'])->unique('category_id') as $category)
                                                <tr>
                                                    <td class="text-start">{{ $category['category_name'] }}</td>
                                                    <td class="text-center
                                                        @if($category['correct_percentage_3rd_month'] >= 98)
                                                            text-success
                                                        @elseif($category['correct_percentage_3rd_month'] >= 88 && $category['correct_percentage_3rd_month'] < 98)
                                                            text-warning
                                                        @else
                                                            text-danger
                                                        @endif
                                                    ">
                                                        {{ number_format($category['correct_percentage_3rd_month'], 0) }}%
                                                    </td>
                                                    <td class="text-center
                                                        @if($category['correct_percentage_2nd_month'] >= 98)
                                                            text-success
                                                        @elseif($category['correct_percentage_2nd_month'] >= 88 && $category['correct_percentage_2nd_month'] < 98)
                                                            text-warning
                                                        @else
                                                            text-danger
                                                        @endif
                                                    ">
                                                        {{ number_format($category['correct_percentage_2nd_month'], 0) }}%
                                                    </td>
                                                    <td class="text-center
                                                        @if($category['correct_percentage_1st_month'] >= 98)
                                                            text-success
                                                        @elseif($category['correct_percentage_1st_month'] >= 88 && $category['correct_percentage_1st_month'] < 98)
                                                            text-warning
                                                        @else
                                                            text-danger
                                                        @endif
                                                    ">
                                                        {{ number_format($category['correct_percentage_1st_month'], 0) }}%
                                                    </td>
                                                    <td class="text-center
                                                        @if($category['correct_percentage_current_month'] >= 98)
                                                            text-success
                                                        @elseif($category['correct_percentage_current_month'] >= 88 && $category['correct_percentage_current_month'] < 98)
                                                            text-warning
                                                        @else
                                                            text-danger
                                                        @endif
                                                    ">
                                                        {{ number_format($category['correct_percentage_current_month'], 0) }}%
                                                    </td>
                                                    <td class="text-center
                                                        @if($category['correct_percentage_current_week'] >= 98)
                                                            text-success
                                                        @elseif($category['correct_percentage_current_week'] >= 88 && $category['correct_percentage_current_week'] < 98)
                                                            text-warning
                                                        @else
                                                            text-danger
                                                        @endif
                                                    ">
                                                        {{ number_format($category['correct_percentage_current_week'], 0) }}%
                                                    </td>
                                                </tr>
                                            @endforeach

                                              </tbody>
                                          </table>
                                      </div>
                                  </div>
                              @endforeach
                          </div>
                      </div>
                  @endforeach
              </div>
          </div>
      </div>
  </div>
</div>
  <div class="row g-2">
                <!-- Audit Chart -->
                <div class="col-md-6 col-6 mb-2">
                  <div class="card">
                    <div class="card-body">
                      <div id="audit-chart"></div>
                    </div>
                  </div>
                </div>
                <!-- /Audit Chart -->
                <!-- Exam Chart -->
                <div class="col-md-6 col-6 mb-2">
                  <div class="card">
                    <div class="card-body">
                      <div id="exam-chart"></div>
                    </div>
                  </div>
               </div>
              </div>
                  <div class="row g-2">
                                <!-- classificationChart -->
                                <div class="col-md-6 col-4 mb-2">
                                  <div class="card h-100">               
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Classification Distribution</h5>
                                    <select class="form-select" style="width: auto;" id="monthSelector">
                                      @foreach($availableMonths as $month)
                                          <option value="{{ $month['value'] }}" {{ $month['value'] == $currentMonth ? 'selected' : '' }}>
                                              {{ $month['label'] }}
                                          </option>
                                      @endforeach
                                  </select>                                  
                                </div>
                                <div class="card-body">
                                    <div id="classificationChart"></div>
                                </div>

                                  </div>
                                </div>
                                <!-- classificationChart -->
                                <!-- Donut Chart -->
                                <div class="col-md-6 col-4 mb-2">
                                  <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                      <h5 class="mb-0">Disposition</h5>
                                      <select class="form-select" style="width: auto;" id="monthSelectorDisposition">
                                        @foreach($availableMonthsDisposition as $month)
                                            <option value="{{ $month['value'] }}" {{ $month['value'] == $currentMonth ? 'selected' : '' }}>
                                                {{ $month['label'] }}
                                            </option>
                                        @endforeach
                                    </select>                                  
                                  </div>
                                  <div class="card-body">
                                    <div id="dispositionChart"></div>
                                  </div>

                                      </div>
                                    </div>

                                  </div>
                               </div>
                              
                                <!-- /Donut Chart -->




@endsection

