function toggleSubMenu(dropdownButton) {
    var submenu = $(dropdownButton).closest('.sideMenu-Item').find('.sub-sideMenu');
    submenu.toggleClass('active');
    var icon = $(dropdownButton).find('i');
    icon.toggleClass('fa-angle-down fa-angle-right');
}