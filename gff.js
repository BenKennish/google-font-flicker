// Google Font Flicker
//
// Ben Kennish
// Nov 2014

console.log("Started gff.js");

// called when a <select> box gets updated.
// it inserts the appropriate <link rel="stylesheet">
// and then updates all elements that match the selector
// to use the new font family
function onSelectNewFontFamily(e)
{
    console.log("onSelectNewFontFamily()", e);

    var selectBox;

    selectBox = e.target;

    if (typeof selectBox.dataset.gffSelector == 'undefined')
    {
        console.error("The select element lacked a 'data-gff-selector' attribute");
        return false;
    }

    var selector = selectBox.dataset.gffSelector;

    // do nothing if they selected the blank option
    if (selectBox.value == '-') return;

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


function gotoNextOrPrevFont(clickEvent)
{
    //console.log('gotoNextOrPrevFont', clickEvent);

    var clickedButton = clickEvent.target;

    if (typeof clickedButton.dataset.gffSelectId != 'undefined')
    {
        var select = document.getElementById(clickedButton.dataset.gffSelectId);

        if (clickedButton.dataset.gffAction == 'next')
        {
            console.log("next request for ", select);
            select.selectedIndex++;
            onSelectNewFontFamily({ target: select });
        }
        else if (clickedButton.dataset.gffAction == 'prev')
        {
            console.log("prev request for ", select);
            select.selectedIndex--;
            onSelectNewFontFamily({ target: select });
        }
        else
        {
            console.error('Unknown gffAction: '+clickedButton.dataset.gffAction);
        }
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

    // find all buttons with data-gff-select-id attributes set
    var buttons = document.getElementsByTagName('button');

    for (var i = 0; i < buttons.length; i++)
    {
        if (typeof buttons[i].dataset.gffSelectId != 'undefined')
        {
            console.log("Attaching gotoNextOrPrevFont to a button");
            buttons[i].addEventListener('click', gotoNextOrPrevFont);
        }
    }

});

console.log("Finished gff.js");
