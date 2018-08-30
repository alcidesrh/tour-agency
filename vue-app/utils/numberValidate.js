export default function (keyCode) {
    return ((keyCode < 48 || (keyCode > 57 && keyCode < 96 || keyCode > 105)) && (keyCode != 8 && keyCode != 46 && keyCode != 37 && keyCode != 39 && keyCode != 13))?true:false;
}