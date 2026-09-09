// Creates the collection chart shell and its initial responsive data view.
export function GhtCollectionChart({ ghtViews, ghtTrend, ghtDays }) {
  const ghtFallbackView = { label: 'Weekly', caption: 'Daily fees collected this week', period: 'Current week', values: ghtTrend, labels: ghtDays };
  const ghtChartViews = ghtViews || window.ghtCollectionChartViews || { weekly: ghtFallbackView };
  const ghtDefaultView = ghtChartViews.monthly || Object.values(ghtChartViews)[0];
  const ghtDefaultKey = ghtChartViews.monthly ? 'monthly' : 'weekly';
  const ghtOptions = Object.entries(ghtChartViews).map(([ghtKey, ghtView]) => `<option value="${ghtKey}">${ghtView.label}</option>`).join('');
  const ghtChart = document.createElement('section');
  ghtChart.className = 'ght-collection-chart mt-7';
  ghtChart.setAttribute('data-ght-chart', '');
  ghtChart.innerHTML = `<div class="ght-chart-heading"><div><p class="ght-chart-kicker">Fees collected</p><p class="ght-chart-caption" data-ght-chart-caption>${ghtDefaultView.caption}</p><p class="ght-chart-period" data-ght-chart-period>${ghtDefaultView.period}</p></div><label class="ght-chart-select-wrap"><span class="sr-only">Chart period</span><select class="ght-chart-select" data-ght-chart-select aria-label="Choose chart period">${ghtOptions}</select><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg></label></div><div class="ght-chart-grid" data-ght-chart-plot>${ghtChartBars({ ghtView: ghtDefaultView })}</div><div class="ght-chart-legend"><span><i class="ght-chart-dot"></i>Collected</span><span class="ght-chart-unit" data-ght-chart-unit>${ghtChartUnit(ghtDefaultView)}</span><span data-ght-chart-high>Highest collection: ${ghtFormatAmount(Math.max(...ghtDefaultView.values))}</span></div>`;
  ghtChart.querySelector('[data-ght-chart-select]').value = ghtDefaultKey;
  Object.defineProperty(ghtChart, 'toString', { value: () => ghtChart.outerHTML });
  return ghtChart;
}

function ghtChartBars({ ghtView }) {
  const ghtMaxValue = Math.max(...ghtView.values, 1);
  return ghtView.values.map((ghtValue, ghtIndex) => `<div class="ght-chart-column"><span class="ght-chart-value">${ghtFormatAmount(ghtValue)}</span><div class="ght-chart-bar${ghtIndex === ghtView.values.length - 1 ? ' ght-chart-bar--current' : ''}" style="height: ${Math.max((ghtValue / ghtMaxValue) * 100, 8)}%" title="${ghtView.labels[ghtIndex]}: ${ghtFormatAmount(ghtValue)} collected"></div><span class="ght-chart-label">${ghtView.labels[ghtIndex]}</span></div>`).join('');
}

function ghtFormatAmount(ghtValue) {
  if (ghtValue >= 1000000) return `₦${(ghtValue / 1000000).toFixed(ghtValue % 1000000 ? 1 : 0)}m`;
  return `₦${Math.round(ghtValue / 1000)}k`;
}

function ghtChartUnit({ values }) {
  return Math.max(...values) >= 1000000 ? 'Amounts in millions of naira' : 'Amounts in thousands of naira';
}

// Binds the period selector and swaps chart data without rebuilding the dashboard.
export function GhtBindCollectionChart({ ghtViews }) {
  const ghtChart = document.querySelector('[data-ght-chart]');
  if (!ghtChart) return;
  const ghtSelect = ghtChart.querySelector('[data-ght-chart-select]');
  const ghtPlot = ghtChart.querySelector('[data-ght-chart-plot]');
  const ghtCaption = ghtChart.querySelector('[data-ght-chart-caption]');
  const ghtPeriod = ghtChart.querySelector('[data-ght-chart-period]');
  const ghtUnit = ghtChart.querySelector('[data-ght-chart-unit]');
  const ghtHighest = ghtChart.querySelector('[data-ght-chart-high]');
  ghtSelect.addEventListener('change', () => {
    const ghtView = ghtViews[ghtSelect.value];
    ghtPlot.innerHTML = ghtChartBars({ ghtView });
    ghtCaption.textContent = ghtView.caption;
    ghtPeriod.textContent = ghtView.period;
    ghtUnit.textContent = ghtChartUnit(ghtView);
    ghtHighest.textContent = `Highest collection: ${ghtFormatAmount(Math.max(...ghtView.values))}`;
  });
}
