document.addEventListener("DOMContentLoaded", function () {

    let posttaskImage = "";

    function posttaskUploadFile() {
        document.getElementById("posttaskFile").click();
    }

    window.posttaskUploadFile = posttaskUploadFile;

    document.getElementById("posttaskFile").addEventListener("change", function () {

        const file = this.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                posttaskImage = e.target.result;

                const img = document.getElementById("posttaskPreview");
                img.src = posttaskImage;
                img.style.display = "block";

                document.getElementById("posttaskText").style.display = "none";
            };

            reader.readAsDataURL(file);
        }

    });

    document.getElementById("posttaskForm").addEventListener("submit", function (e) {
        e.preventDefault();
        alert("Task submitted!");
    });

});