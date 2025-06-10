const mainform = document.querySelector('.mainform');
const loginLink = document.querySelector('.login-link');
const registerLink = document.querySelector('.register-link');

registerLink.addEventListener('click', ()=> {
    mainform.classList.add('active');
});

loginLink.addEventListener('click', ()=> {
    mainform.classList.remove('active');
});


