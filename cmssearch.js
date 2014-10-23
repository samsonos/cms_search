/**
 * Created by onysko on 22.10.2014.
 */

var __SansonCMS_searchLoader;

s('.samson_CMS_searchInput').pageInit(function(search) {
    // Prepare main block
    parentPrepare(search);
    var timeOutProgress = 0;

    var name = search.a('name');

    var codeArray = [
        113, 114, 115, 116, 118, 120, 16, 17, 18, 91, 20, 37, 38, 39, 40, 27, 44, 33, 34, 35, 36, 45, 46, 13, 144
    ];

    search.keyup(function(search, params, e) {
        s.trace(e.keyCode);
        if (codeArray.indexOf(e.keyCode) == -1) {
            // Send only one AJAX request
            if (timeOutProgress == 0) {
                timeOutProgress = 1;

                // Build request URL
                var searchAction = search.a('preview-action') + '?' + name + '=' + search.val();

                // Set 1 second timeout
                var searchTimeOut = setTimeout(function() {
                    if (__SansonCMS_searchLoader.length) {
                        __SansonCMS_searchLoader.show();
                    }

                    // If value is not empty - find
                    if (search.val() != '') {
                        // Send request
                        s.ajax(searchAction, function(response){
                            // Parse AJAX response
                            response = JSON.parse(response);

                            // Show preview block only we we have some found
                            if (response.html != '') {
                                s('.samson_CMS_searchPreview').show();
                            } else {
                                s('.samson_CMS_searchPreview').hide();
                            }

                            // Show founded items
                            s('.samson_CMS_searchPreviewItems').html(response.html);
                            timeOutProgress = 0;

                            if (__SansonCMS_searchLoader.length) {
                                __SansonCMS_searchLoader.hide();
                            }
                        });
                    }
                }, 1500);
            }
        }
    });

    s('html').click(function( obj, opt, e )
    {
        var clickedElement = s(e.srcElement||e.originalTarget);

        if (clickedElement != undefined
                && !clickedElement.hasClass('samson_CMS_searchPreview')
                && !clickedElement.hasClass('yasearch samson_CSM_searchBlock')
                && !clickedElement.hasClass('samson_CMS_searchPreviewItems')
                && !clickedElement.hasClass('search_preview_item')) {
            s('.samson_CMS_searchPreview').hide();
        }
    });
});

function parentPrepare(search) {
    // Get parent of search input
    var parent = search.parent();

    // Add specified class into parent block
    parent.addClass('samson_CSM_searchBlock');

    // Create top offset of parent block
    var topOffset = search.height() + 3;

    // Create preview block
    var searchPreview = s('<div class="samson_CMS_searchPreview">');
    searchPreview.css('top', topOffset);
    searchPreview.css('display', 'none');

    // Create founded items view
    var searchPreviewItems = s('<div class="samson_CMS_searchPreviewItems">');
    searchPreview.append(searchPreviewItems);

    // Try to get search loader
    __SansonCMS_searchLoader = s('.samson_CMS_searchLoader');

    // Add loader into parent block
    if (__SansonCMS_searchLoader.length) {
        parent.append(__SansonCMS_searchLoader);
    }

    // Add created blocks into parent
    parent.append(searchPreview);
}
