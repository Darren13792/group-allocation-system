function getSuffix(date) {
    switch (date % 10) {
        case 1:
            return 'st';
        case 2:
            return 'nd';
        case 3:
            return 'rd';
        default:
            return 'th';
        }
}

function formatDate(inputDateString) {
    var inputDate = new Date(inputDateString);
    var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    var day = days[inputDate.getDay()];
    var date = inputDate.getDate();
    var suffix = getSuffix(date);
    var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var month = monthNames[inputDate.getMonth()];
    var year = inputDate.getFullYear();
    var hours = inputDate.getHours();
    var minutes = inputDate.getMinutes().toString().padStart(2, '0');

    var formattedDate = day + ', ' + date + suffix + ' ' + month + ' ' + year + ', ' + hours + ':' + minutes;
    return formattedDate;
}
export { formatDate };