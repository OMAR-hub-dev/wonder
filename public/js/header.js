const burger = document.querySelector('.burger');
const menu = document.querySelector('.menu-xs');
const appContent = document.querySelector('.app-content');


burger.addEventListener('click', (event) =>{
    menu.classList.toggle('hidden');
})

appContent.addEventListener('click', (event) => {
    if (!menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
    }
})