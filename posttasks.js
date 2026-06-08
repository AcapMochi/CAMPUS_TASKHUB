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

// submit (CONNECT TO MYTASKS)
document.getElementById("posttaskForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const task = {
        title: document.getElementById("title").value,
        category: document.getElementById("category").value,
        desc: document.getElementById("desc").value,
        location: document.getElementById("location").value,
        reward: document.getElementById("reward").value,
        image: imageData,
        status: "open"
    };

    let tasks = JSON.parse(localStorage.getItem("posttasks")) || [];

    tasks.push(task);

    localStorage.setItem("posttasks", JSON.stringify(tasks));

    alert("Task submitted!");

    this.reset();
    document.getElementById("preview").style.display = "none";
});