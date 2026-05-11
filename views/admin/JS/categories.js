document.getElementById('catForm').addEventListener('submit', function(e) {
    if (!valid())
   {
        e.preventDefault();
    }
});

function valid(){
    const name = document.getElementById('name').value.trim();
    const description = document.getElementById('description').value.trim();

    let f = true ; 
    if(name === '' || description === ''){
        f = false ; 
        document.getElementsByClassName('error')[0].innerHTML = 'All fields are required';
    }
    return f;
}