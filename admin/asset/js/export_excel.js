function export_excel(id) {
    $(id).table2excel({
        exclude: ".noExl",
        name: "Worksheet Name",
        filename: "SomeFile", //do not include extension
        fileext: ".xlsx" // file extension
    });
}