let imageData = "";

// upload
function uploadFile() {
    document.getElementById("fileInput").click();
}

// preview
document.getElementById("fileInput").addEventListener("change", function () {

    const file = this.files[0];

    if (file) {
        const reader = new FileReader();

        reader.onload = function (e) {
            imageData = e.target.result;

            const preview = document.getElementById("preview");
            preview.src = imageData;
            preview.style.display = "block";

            document.getElementById("uploadText").style.display = "none";
        };

        reader.readAsDataURL(file);
    }

});

document.getElementById("posttaskForm").addEventListener("submit", function (e) {
    e.preventDefault();

    // Create a FormData object to easily send form fields to PHP
    let formData = new FormData();
    formData.append("title", document.getElementById("title").value);
    formData.append("category", document.getElementById("category").value);
    formData.append("desc", document.getElementById("desc").value);
    formData.append("location", document.getElementById("location").value);
    formData.append("reward", document.getElementById("reward").value);
    
    // Note: If you want to handle the image (imageData), you can append it here too, 
    // but you will need to add an 'image' column to your database later!

    // Send the data to the PHP handler
    fetch("includes/post_task_handler.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert("Task submitted to the marketplace!");
            document.getElementById("posttaskForm").reset();
            document.getElementById("preview").style.display = "none";
            document.getElementById("uploadText").style.display = "block";
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error("Error posting task:", error);
        alert("Something went wrong. Please try again.");
    });
});