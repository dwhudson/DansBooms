document.addEventListener('DOMContentLoaded', () => {
  const button = document.getElementById('btn-click');
  const output = document.getElementById('output');

  button.addEventListener('click', () => {
    output.textContent = 'App initialized successfully!';
  });
});