console.log("Started gff.js");


// called when a <select> box gets updated.
// it inserts the appropriate <link rel="stylesheet">
// and then updates all elements that match the selector
// to use the new font family
function onSelectNewFontFamily(e)
{
    console.log("onSelectNewFontFamily()", e);

    var selectBox = e.target;
    if (typeof selectBox.dataset.gffSelector == 'undefined')
    {
        console.error("The select element lacked a 'data-gff-selector' attribute");
        return false;
    }

    var selector = selectBox.dataset.gffSelector;

    // insert the appropriate stylesheet as a <link> within <head>
    // (TODO: check if it already exists in the DOM!)
    var newStyleSheet = document.createElement('link');
    newStyleSheet.rel = "stylesheet";
    var stylesheetURL = "//fonts.googleapis.com/css?family="+encodeURIComponent(selectBox.value);
    console.log('Stylesheet URL: ', stylesheetURL);
    newStyleSheet.href = stylesheetURL;
    // insert as last element of <head> tag
    document.getElementsByTagName('head')[0].appendChild(newStyleSheet);

    // set all elements matching the selector to the new font family
    var elements = document.querySelectorAll(selector);
    for (var i = 0; i < elements.length; i++)
    {
        console.log(elements[i]);
        elements[i].style.fontFamily = selectBox.value;
        //TODO: allow changing font weight and italics and stuff
    }
}

document.addEventListener("DOMContentLoaded", function(event)
{
    // add listeners to call selectNewFontFamily() whenever
    // user updates a <select> that has a data-gff-query=".." attribute
    console.log("DOM fully loaded and parsed!");
    console.log("Adding event listeners to <select> elements...");

    var selectBoxes = document.getElementsByTagName('select');

    for (var i = 0; i < selectBoxes.length; i++)
    {
        if (typeof selectBoxes[i].dataset.gffSelector != 'undefined')
        {
            selectBoxes[i].addEventListener('change', onSelectNewFontFamily);
        }
    }

});

console.log("Finished gff.js");
