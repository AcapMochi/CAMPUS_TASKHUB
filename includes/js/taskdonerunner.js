
const MockDatabaseResponse =
{
    taskName: "Beli Barang",
    taskRunner: "Bangcok",
    taskDuration: "60 minutes",
    taskRating: "5",
    taskPrice: "RM20.00"
}

function LoadReceiptData(taskDetails)
{
    document.getElementById('taskName').textContent = taskDetails.taskName;
    document.getElementById('taskRunner').textContent = taskDetails.taskRunner;
    document.getElementById('taskDuration').textContent = taskDetails.taskDuration;
    document.getElementById('taskPrice').textContent = taskDetails.taskPrice;
    document.getElementById('taskRating').textContent = taskDetails.taskRating;
}

LoadReceiptData(MockDatabaseResponse);

document.getElementById('browseMoreBtn').addEventListener('click', () =>
{
    window.location.href = 'browseTask.html';
});