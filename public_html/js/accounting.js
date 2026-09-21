// ログアウトの確認
document.getElementById('logoutForm').addEventListener('submit', function(event) {
 if (!confirm('ログアウトしますか？')) {
  event.preventDefault();
 }
});