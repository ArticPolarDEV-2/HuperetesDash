const menuButton = document.getElementById('menu-toggle');
const sidebar = document.querySelector('.sidebar');

menuButton.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    menuButton.classList.toggle('active');
});