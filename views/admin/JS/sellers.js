let actionLinks = document.querySelectorAll(".actionLink");

for (let i = 0; i < actionLinks.length; i++) {

    actionLinks[i].onclick = function(event) {
        event.preventDefault();
        let id = this.getAttribute("data-id");
        let action = this.getAttribute("data-action");

        let xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            let row = document.getElementById("sellerRow" + id);
            let statusCell = row.querySelector(".status");
            if (action === "approve") {
                statusCell.innerHTML = "Approved";
            } 
            else if (action === "reject") {
                statusCell.innerHTML = "Rejected";
            } 
            else if (action === "suspend") {
                statusCell.innerHTML = "Suspended";
            } 
            else if (action === "reactivate") {
                statusCell.innerHTML = "Approved";
            }
        }; 
        xhttp.open(
            "GET",
            "../../controller/adminController/sellersController.php?action="
            + action + "&id=" + id,
            true
        );
        xhttp.send();
    };
}