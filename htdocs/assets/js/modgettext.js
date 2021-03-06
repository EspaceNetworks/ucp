/**
 * FreePBX module based gettext class
 *
 * short translates based on a modules domain self initializing
 *
 * Much of this code was ported from modgettext.class.php in FreePBX
 * the license from FreeePBX applies forward
 *
 * @author Philippe Lindheimer
 * @author Andrew Nagy
 */

var langDomain,
		textdomain_stack = [];
/**
 * _() translate a string
 * short translates a string given the string
 *
 * @param	string
 * @param string module
 * @return string
 *
 * Given a string, this function will attempt to translate the string
 * using the module's textdomain if translations exist. If it finds the translated text
 * is the same as the original text AND the domain is NOT ucp
 * then it will make a last attempt to lookup a translation in ucp
 */
function _(string, module) {
	var domain = (typeof module !== "undefined") ? module : textdomain(null);
	try {
		var tstring = "";
		tstring = UCP.i18n.dgettext( domain, string );
		// if our translation didn't change and we aren't already using 'ucp' then try with ucp
		if (tstring == string && domain != "ucp") {
			tstring = UCP.i18n.dgettext("ucp", string);
		}
		return tstring;
	} catch (err) {
		//no translations so just query ucp
		try {
			return UCP.i18n.dgettext("ucp", string);
		} catch (err) {
			return string;
		}
	}
}

function sprintf() {
	try {
		return UCP.i18n.sprintf.apply(this, arguments);
	} catch (err) {
		return string;
	}
}

/**
 * textdomain
 * short sets the textdomain to the domain defined for this module
 *
 * @param string The new message domain, or NULL to get the current setting without changing it
 * @return string
 */
function textdomain(module) {
	if(typeof module !== "undefined" && module !== null) {
		langDomain = module;
	}
	return langDomain;
}

/**
 *
 * push_textdomain
 * short sets the textdomain while saving the current domain on a stack
 *
 * @param string
 * @return string
 */
function push_textdomain(module) {
	textdomain_stack.push(textdomain(null));
	return textdomain(module);
}

/**
 *
 * pop_textdomain
 * short resets the textdomain to the previous value or leaves unchanged
 *
 * @return string
 */
function pop_textdomain() {
	// if array is empty then null is returned to textdomain() which is the desired affect
	return textdomain(textdomain_stack.pop());
}
