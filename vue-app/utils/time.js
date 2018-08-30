export default function () {
    let hour = new Date().getHours();
    let minutes = new Date().getMinutes();
    let seconds = new Date().getSeconds()
    return hour + ':' + minutes + ':' + seconds;
}
