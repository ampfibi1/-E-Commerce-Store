let searchUser = document.getElementById('search_user');

if (searchUser) {
    searchUser.addEventListener('keyup', searchSuggestion);
}

function searchSuggestion() {
    const txtForSearch = this.value.trim();
    const suggestionBox = document.getElementById('suggestion_box');
    if (txtForSearch === "") {
        suggestionBox.innerHTML = "";
        return;
    }
    const xmlhttp = new XMLHttpRequest();
    xmlhttp.onload = function () {
        try {
            let datas = JSON.parse(this.responseText);
            suggestionBox.innerHTML = "";

            for (let i = 0; i < datas.length; i++) {
                suggestionBox.innerHTML += datas[i] + "<br>";
            }
        } catch (error) {
            console.log("Invalid JSON Response");
            console.log(this.responseText);
        }
    };
    xmlhttp.open(
        "GET",
        "../../controller/adminController/userSearchController.php?q="
        + txtForSearch ,
        true
    );
    xmlhttp.send();
}

let actionLinks = document.querySelectorAll(".actionLink");

for (let i = 0; i < actionLinks.length; i++) {
    actionLinks[i].onclick = function (event) {
        event.preventDefault();

        let id = this.getAttribute("data-id");
        let action = this.getAttribute("data-action");
        let currentButton = this;
        let xhttp = new XMLHttpRequest();

        xhttp.onload = function () {
            let row = document.getElementById("userRow" + id);
            let statusCell = row.querySelector(".status");
            if (action === "deactivate") {
                statusCell.innerHTML = "No";
                currentButton.innerHTML = "Reactivate";
                currentButton.setAttribute(
                    "data-action",
                    "reactivate"
                );
                currentButton.classList.remove("deactivate");
                currentButton.classList.add("reactivate");
            } else if (action === "reactivate") {
                statusCell.innerHTML = "Yes";
                currentButton.innerHTML = "Deactivate";
                currentButton.setAttribute(
                    "data-action",
                    "deactivate"
                );
                currentButton.classList.remove("reactivate");
                currentButton.classList.add("deactivate");
            }
        };
        xhttp.open(
            "GET",
            "../../controller/adminController/usersController.php?action="
            + action + "&id=" + id,
            true
        );
        xhttp.send();
    };
}