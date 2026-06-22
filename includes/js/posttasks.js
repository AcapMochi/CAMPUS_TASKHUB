// includes/posttasks.js
let selectedFile = null;

// trigger file input
function uploadFile() {
    document.getElementById("fileInput").click();
}

// preview and store file
document.getElementById("fileInput").addEventListener("change", function () {
    const file = this.files[0];

    if (file) {
        selectedFile = file; // Store the actual file for the server

        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById("preview");
            preview.src = e.target.result;
            preview.style.display = "block";
            document.getElementById("uploadText").style.display = "none";
        };
        reader.readAsDataURL(file);
    }
});

// submit form
document.getElementById("posttaskForm").addEventListener("submit", function (e) {
    e.preventDefault();

    let formData = new FormData();
    formData.append("title", document.getElementById("title").value);
    formData.append("category", document.getElementById("category").value);
    formData.append("desc", document.getElementById("desc").value);
    formData.append("location", document.getElementById("location").value);
    formData.append("specific_location", document.getElementById("specific_location").value);
    formData.append("reward", document.getElementById("reward").value);
    
    // If a file was selected, append it! Fetch will automatically set the headers for multipart/form-data.
    if (selectedFile) {
        formData.append("attachment", selectedFile);
    }

    const submitBtn = document.querySelector('.continue');
    submitBtn.textContent = "Posting...";
    submitBtn.disabled = true;

    fetch("includes/php/post_task_handler.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert("Task submitted to the marketplace!");
            window.location.href = "mytasks.html"; // Redirect to see the newly posted task
        } else {
            alert("Error: " + data.message);
            submitBtn.textContent = "Continue";
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error("Error posting task:", error);
        alert("Something went wrong. Please try again.");
        submitBtn.textContent = "Continue";
        submitBtn.disabled = false;
    });
});