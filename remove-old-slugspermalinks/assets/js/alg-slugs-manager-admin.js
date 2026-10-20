/**
 * Slugs Manager - Admin JS
 *
 * @version 2.9.0
 * @since   2.9.0
 *
 * @author WPFactory
 */

jQuery("#alg-slugs-manager-select-all").change(function () {
	if (jQuery(this).prop("checked")) {
		jQuery("input.alg-slugs-manager-post-checkbox").prop("checked", true);
	} else {
		jQuery("input.alg-slugs-manager-post-checkbox").prop("checked", false);
	}
});
jQuery("#alg-slugs-manager-select-all").trigger("change");
