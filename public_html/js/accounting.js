// ログアウトの確認
document.getElementById('logoutForm').addEventListener('submit', function(event) {
 if (!confirm('ログアウトしますか？')) {
  event.preventDefault();
 }
});


console.log('accounting.js 読み込みOK');

console.log(
    document.getElementById('calendarButton')
);

console.log(
    document.getElementById('calendarInput')
);


/*
 * カレンダーアイコンの処理
 */

 const calendarButton =
     document.getElementById('calendarButton');

 const calendarInput =
     document.getElementById('calendarInput');


 calendarButton.addEventListener('click', () => {
 
 console.log('カレンダーボタンをクリックしました');

 calendarInput.showPicker();
 
 console.log('showPicker実行しました');

 });


 calendarInput.addEventListener('change', () => {

  if (!calendarInput.value) {
      return;
  }
 
  const selectedDate = calendarInput.value;
 
  const url = new URL(window.location.href);
 
  url.searchParams.set('date', selectedDate);
  url.searchParams.set('mode', 'day');
 
  url.searchParams.delete('nav');
 
  window.location.href = url.toString();

 });