(function($) {
    /**
     * Ajax callback for ACL security identity typeaheads.
     *
     * Looks for data-aclsid attribute which holds Ajax callback URL.
     */
    $(function() {
        // Get base information
        var acl_container = $('[data-aclsid]'),
            acl_provider = acl_container.data('aclsid');

        // Typeahead Ajax source function, gets query term and process callback
        // as parameters
        var provider = function(query, process_callback) {
            return $.get(acl_provider, {
                query: query
            }, function(data) {
                console.log(data);
                return process_callback(data);
            });

        };

        // Create a typeahead for every SID input
        $('[data-provide="typeahead"]', acl_container).each(function() {
            var typeahead = $(this).typeahead({
                source: provider,
                minLength: 3
            });
        });
    });
})(jQuery);
