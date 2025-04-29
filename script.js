const przycisk = document.getElementById('przycisk');

przycisk.addEventListener('click', function(){
    alert('Przycisk zostal klikniety!');
    przycisk.textContent = "Dziekuje za klikniecie;"
});