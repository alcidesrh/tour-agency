export default function (item, queryText, itemText) {
    const hasValue = val => val != null ? val : ''
    const text = hasValue(item.name)
    const query = hasValue(queryText)
    return text.toString()
        .toLowerCase()
        .indexOf(query.toString().toLowerCase()) > -1
}