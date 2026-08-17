// クエリパラメータを取得
const params = new URLSearchParams(window.location.search);
const period = params.get('period');
const btnname = params.get('btnname');

console.log('btnname:'.btnname);

fetch(`./inc/_admin.php?period=${encodeURIComponent(period)}&btnname=${encodeURIComponent(btnname)}`)
.then(res => res.json())

.then(data => {
  const max = Math.max(...data.counts, 1); // 0除算防止
  const chart = document.createElement('div');
  chart.className = 'bar-chart';

  const daysOfWeek = ["日", "月", "火", "水", "木", "金", "土"];

  data.counts.forEach((count, i) => {
    const barWrapper = document.createElement('div');
    barWrapper.className = 'bar-item';
    const bar = document.createElement('div');
    bar.className = 'bar';
    bar.style.height = (count / max * 100) + '%';
    bar.innerHTML = `<span>${count}</span>`;

    const label = document.createElement('div');
    label.className = 'bar-label';
    label.textContent = data.labels[i].split('/').slice(1).map(n => parseInt(n, 10)).join('/');
    const day = data.labels[i].split('/').slice(1).map(n => parseInt(n, 10)).join('月') + '日';
    const dateStr = data.labels[i];
    const dateObj = new Date(dateStr);
    const weekday = daysOfWeek[dateObj.getDay()];
    bar.setAttribute('data-tooltip', `${day}(${weekday}): ${count}件`);
    label.setAttribute('data-weekday', weekday);


    barWrapper.appendChild(bar);
    barWrapper.appendChild(label);
    chart.appendChild(barWrapper);
  });

  document.getElementById('chart-container').appendChild(chart);
})
  
.catch(err => {
  console.error('fetch失敗:', err);
});