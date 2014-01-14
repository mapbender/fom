(function($) {

$(document).on('click', '.collectionAdd', function(event) {
    event.preventDefault();

    // Gather all needed information, like
    //  collection we're handling right now...
    var collection = $(event.target).parent(),
        // The prototype text for the new item...
        prototype = collection.data('prototype'),
        // The index, which might be undefined first...
        index = collection.data('index') || 0,

        // And finally parse the prototype into a new clean item for insertion.
        item = $($.parseHTML(prototype
            .trim()
            .replace(/__name__label__/g, '')
            .replace(/__name__/g, index))[0])
            .addClass('collectionItem');

    // Now let's enter that item...
    collection.append(item);
    // And update our counter.
    collection.data('index', index + 1);
});

$(document).on('click', '.collectionRemove', function(event) {
    event.preventDefault();

    // Get the item...
    var item = $(event.target).closest('.collectionItem');
    // And remove it.
    item.remove();
});

})(jQuery);
