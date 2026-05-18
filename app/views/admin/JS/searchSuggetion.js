document.getElementById('search').addEventListener('keyup', searchSuggestion);

function searchSuggestion() {
    const txtForSearch = this.value.trim();
    const suggestionBox = document.getElementById('suggestion_box');
    
    if (txtForSearch === "") {
        suggestionBox.innerHTML = "";
        return;
    }

    const xmlhttp = new XMLHttpRequest();

    xmlhttp.onload = function () {
        let datas = JSON.parse(this.responseText);
        suggestionBox.innerHTML = "";

        for (let i = 0; i < datas.length; i++) {
            suggestionBox.innerHTML += datas[i] + "<br>";
        }
    }

    xmlhttp.open("GET","?c=admin&a=suggest&q=" + encodeURIComponent(txtForSearch) ,
        true
    );

    xmlhttp.send();
}