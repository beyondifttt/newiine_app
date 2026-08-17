function submitSetting() {
  var newpw = document.getElementById('newpw').value;
  var confirmPw = document.getElementById('confirm-pw').value;
  var banIPInput = document.getElementsByName('banIP')[0];
  var limitpostInput = document.getElementById('limitpost');
  var saveperiodInput = document.getElementById('saveperiod');

  // 新パスワード確認
  if (newpw !== '') {
    if (confirmPw === '') {
      alert('新パスワードは確認のため二度入力してください。');
      return false;
    } else if (newpw !== confirmPw) {
      alert('新パスワードが一致しません。再度入力してください。');
      return false;
    }
  }

  // banIPの入力がある場合はIPアドレス形式をチェック
  if (banIPInput && banIPInput.value !== '') {
    
    // IPアドレス形式（ワイルドカード対応）の正規表現
    const ipRegex = /^(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9]|\*)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9]|\*)){3}$/;
    var banIP = banIPInput.value;

    // IPアドレス形式の確認
    if (!ipRegex.test(banIP)) {
      alert('IPアドレスの形式が不正です。');
      return false;
    }
  }

  // limitpostのバリデーション
  if (limitpostInput) {
    var limitpost = parseInt(limitpostInput.value, 10);
    if (isNaN(limitpost) || limitpost <= 0) {
      alert('１日のいいね数上限は正の整数で設定してください。');
      return false;
    }
  }

  // saveperiodのバリデーション
  if (saveperiodInput) {
    var saveperiod = parseInt(saveperiodInput.value, 10);
    if (isNaN(saveperiod) || saveperiod <= 0) {
      alert('いいねログデータの保存日数は正の整数で設定してください。');
      return false;
    }
  }

  // 最終確認
  return confirm("設定を変更しますか？");
}
