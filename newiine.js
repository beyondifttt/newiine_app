document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  const visibleTime = 6000;
  // お礼メッセージを表示する時間の長さを変更できます（単位はミリ秒。6000＝6秒）


  //========================================================//
  // ここから下は基本的に触らないでください！
  //========================================================//
  

  const script = document.querySelector('script[src$="newiine.js"]');
  const root = script?.src.match(/(^|.*\/)newiine\.js$/)?.[1] || '';
  const ajaxPath = `${root}_ajax.php`;
  const getToken = `${root}get-token.php`;
  let currentUrl = location.href;

  const buttons = Array.from(document.querySelectorAll('.newiine_btn'));

  
  fetch(getToken, { method: 'POST' })
    .then(res => res.json())
    .then(data => {
      const token = data.token;
      document.querySelectorAll('.newiine_btn').forEach(btn => {
        btn.dataset.token = token;
      });
    });

  buttons.forEach((button, index) => {
    const name = button.dataset.iinename;
    const countEl = button.querySelector('.newiine_count');
    const thanksEl = button.querySelector('.newiine_thanks');
    const boxes = thanksEl ? Array.from(thanksEl.querySelectorAll('.newiine_box')) : [];
    let messageShown = false;

    // 初期カウント取得
    fetch(`${ajaxPath}?buttonname=${encodeURIComponent(name)}`)
      .then(res => res.json())
      .then(([count, clickedToday]) => {
        if (countEl) countEl.textContent = count;
        if (clickedToday) button.classList.add('newiine_clickedtoday');
      })
      .catch(err => {
        alert('いいねボタン改エラー：\nお使いのサーバーでPHPが使えるか、設置方法をご確認ください。');
        console.error(err);
      });

    // ボタンクリック時
    button.addEventListener('click', e => {
      e.preventDefault();
      const token = button.dataset.token;

      const btnUrl = button.dataset.iineurl || currentUrl;
      const countLimit = parseInt(button.dataset.iinecountlimit, 10) || false;

      const data = new URLSearchParams({
        path: btnUrl,
        buttonname: name,
        iineNewCountLimit: countLimit ?? '',
        token: token,
        mode: 'check'
      });

      fetch(ajaxPath, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: data.toString()
      })
        .then(res => res.text())
        .then(res => {
          try {
            const [count] = JSON.parse(res);
            if (countEl) countEl.textContent = count;

            button.classList.add('newiine_clicked', 'newiine_animate');
            setTimeout(() => button.classList.remove('newiine_animate'), 500);

            // 同じ名前の他ボタンにも反映
            buttons.forEach((otherBtn, i) => {
              if (i !== index && otherBtn.dataset.iinename === name) {
                otherBtn.classList.add('newiine_clicked');
                const otherCountEl = otherBtn.querySelector('.newiine_count');
                if (otherCountEl) otherCountEl.textContent = count;
              }
            });

            if (thanksEl) {
              thanksEl.style.display = 'block';
              if (!messageShown) fadeOut(thanksEl, index);
              if (boxes.length > 1 && !messageShown) {
                const rand = Math.floor(Math.random() * boxes.length);
                boxes.forEach((box, i) => {
                  box.style.display = i === rand ? 'block' : 'none';
                });
              }
              messageShown = true;
            }
          } catch (err) {
            console.warn('いいねボタン処理エラー:', res);
          }
        })
        .catch(() => {
          alert('いいねボタン改エラー：\nいいねボタンの設置方法が正しいかご確認ください。');
        });
    });

    function fadeOut(el, i) {
      setTimeout(() => el.classList.add('newiine_fadeout'), visibleTime);
      setTimeout(() => {
        el.style.display = 'none';
        el.classList.remove('newiine_fadeout');
        messageShown = false;
      }, visibleTime + 500);
    }
  });
});
