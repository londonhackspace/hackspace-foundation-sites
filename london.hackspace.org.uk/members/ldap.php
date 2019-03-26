<?
$page = 'ldap';
$title = 'LDAP Account Settings';
$extra_head = '
        <script type="text/javascript" src="/javascript/md4.js"></script>
        <script type="text/javascript" src="/javascript/sha1.js"></script>
        <script type="text/javascript" src="/javascript/enc-base64.js"></script>
';
require( '../header.php' );

if (!$user) {
    fURL::redirect('/login.php?forward=/members/ldap.php');
}
?>
<h2>LDAP Account</h2>

<div id="result"></div>

<script>

function calculateHashes()
{

        var fields = ["username", "nt_password", "ssha_password"];

        var out = "";

        for (var i = 0; i < fields.length; i++) {
            var val = document.getElementById(fields[i]).value;
            if (val === "") {
                out += "<p>Field " + fields[i] + " must be filled in.</p>";
            }
        }

        if (out != "") {
            bad_things(out);
            return;
        }

	/* this is:

	NThash=MD4(UTF-16-LE(password))

	- or -

	echo -n "fish1234" | iconv -t utf-16le | openssl md4

	It does appear that hex_md4 and friends do do utf-16le.

	*/
	var nt_password = document.getElementById("nt_password").value;
	var NTLMHash = "";
	NTLMHash = hex_md4(nt_password);

	document.getElementById("ldapnthash").value = NTLMHash;

	/* SSHA infos:

		http://www.openldap.org/faq/data/cache/347.html

		https://code.google.com/p/crypto-js/

	 */
	var ssha_password = document.getElementById("ssha_password").value;

	var salt = CryptoJS.lib.WordArray.random(4);
	var shahash = CryptoJS.algo.SHA1.create();
	shahash.update(ssha_password);
	shahash.update(salt);

	var hash = shahash.finalize();

	hash = hash.concat(salt);

	var out = "{SSHA}" + CryptoJS.enc.Base64.stringify(hash);

	document.getElementById("ldapsshahash").value = out;

	document.getElementById("ldapuser").value = document.getElementById("username").value;

	document.getElementById("ldapshell").value = document.getElementById("shell").value;

	document.getElementById("ldapemail").value = document.getElementById("email").value;

	var form = document.getElementById("ldapform");
	form.submit();
}

function bad_things(message) {
    document.getElementById("result").innerHTML = message;
    document.getElementById("result").className = "alert alert-danger";
}

function all_ok(message) {
    document.getElementById("result").innerHTML = message;
    document.getElementById("result").className = "alert alert-success";
}

</script>

<?

$shells = array('/bin/bash', '/bin/sh', '/bin/zsh');

if($user->isMember()) {

    $user_profile = $user->createUsersProfile();
    if ($user_profile->getAllowEmail() && $user->getLdapemail() == '') {
        $email = $user->getEmail();
    } else {
        $email = $user->getLdapemail();
    }

    // Link or unlink a user.
    if( array_key_exists( 'create', $_POST ) && array_key_exists( 'token', $_POST )) {
        $ok = false;
        try {
            fRequest::validateCSRFToken($_POST['token']);
            $validator = new fValidation();
            $validator->addRequiredFields( 'ldapuser', 'ldapnthash', 'ldapsshahash', 'ldapshell', 'ldapemail');
            $validator->addEmailFields('ldapemail');
            $validator->validate();

            // Attempt account creation and promotion.
            if (!preg_match('/^[a-z][a-z0-9_-]{0,31}$/', $_POST['ldapuser'])) {
                throw new fValidationException( '<p>The username must only contain a-z, 0-9 _ and -.</p>' );
            }

            $not_allowed_names = array(
                # system accounts
                "root" => 1,
                "daemon" => 1,
                "bin" => 1,
                "sys" => 1,
                "sync" => 1,
                "games" => 1,
                "man" => 1,
                "lp" => 1,
                "mail" => 1,
                "news" => 1,
                "uucp" => 1,
                "proxy" => 1,
                "www-data" => 1,
                "backup" => 1,
                "list" => 1,
                "irc" => 1,
                "gnats" => 1,
                "nobody" => 1,
                "libuuid" => 1,
                "sshd" => 1,
                "ntp" => 1,
                "messagebus" => 1,
                "colord" => 1,
                "saned" => 1,
                "openldap" => 1,
                # and some other bits
                "avahi" => 1,
                "mpd" => 1,
                "radvd" => 1,
                "quasselcore" => 1,
                "statd" => 1,
                "ntop" => 1,
                "postgres" => 1,
                "bitlbee" => 1,
                "smokeping" => 1,
                "debian-exim" => 1,
                "snmp" => 1,
                "asterisk" => 1,
                "debian-tor" => 1,
                "privoxy" => 1,
                "bind" => 1,
                "dhcpd" => 1,
                "ircensus" => 1,
                "cacti" => 1,
                "mysql" => 1,
                "hplip" => 1,
                "haldaemon" => 1,
                "mosquitto" => 1,
                "postfix" => 1,
                # lhs system accounts
                "glados" => 1,
                "boarded" => 1,
                "board" => 1,
                "bmeter" => 1,
                "netometer" => 1,
                "robonaut" => 1,
                # net stuff
                "postmaster" => 1,
                "hostmaster" => 1,
                "webmaster" => 1,
                "abuse" => 1,
                "spam" => 1,
                # could be used to troll etc (if we ever do member email accounts),
                # an infinate number of these :/
                "billing" => 1,
                "accounts" => 1,
                "support" => 1,
                "techsupport" => 1,
                "trustees" => 1,
                "noc" => 1,
                "security" => 1,
                "directors" => 1,
                "contact" => 1,
                "info" => 1,
                "property" => 1,
                "ebay" => 1,
                "elections" => 1,
                "accounts" => 1,
                "membership" => 1,
                "sysadmin" => 1,
                "anonymous" => 1,
                "anon" => 1,
                "administrator" => 1,
                "admin" => 1
            );

            if (array_key_exists(strtolower($_POST['ldapuser']), $not_allowed_names)) {
                throw new fValidationException( '<p>You are not allowed to use '.htmlspecialchars($_POST['ldapuser']).' as a username.</p>' );
            }

            if (!in_array($_POST['ldapshell'], $shells)) {
                throw new fValidationException( '<p>'.htmlspecialchars($_POST['ldapshell']).' is not a valid shell.</p>' );
            }

            if (!preg_match('/^[A-F0-9]{32}$/', $_POST['ldapnthash'])) {
                throw new fValidationException( '<p>That dosn\'t look like an NT hash</p>' );
            }

            if (!preg_match('/^\{SSHA\}[A-Za-z0-9+\/]{32}$/', $_POST['ldapsshahash'])) {
                throw new fValidationException( '<p>That dosn\'t look like an SSHA hash</p>' );
            }

            # do they have an existing ldapusername?
            $eluser = $user->getLdapuser();
            if (isset($eluser)) {
                if ($eluser !== $_POST['ldapuser']) {
                    throw new fValidationException( '<p>Your LDAP username must match your existing one: '. htmlspecialchars($eluser) .'.</p>' );
                }
            }

            $username = escapeshellarg( $_POST['ldapuser'] );
            $nthash = escapeshellarg( $_POST['ldapnthash'] );
            $sshahash = escapeshellarg( $_POST['ldapsshahash'] );
            $shell = escapeshellarg( $_POST['ldapshell'] );
            $ldapemail = escapeshellarg( $_POST['ldapemail'] );

            $uid = $user->getId();
            $uid += 100000;
            $uid = escapeshellarg( $uid );

            $success = trim( shell_exec( "sudo -g ldapadmin /var/www/hackspace-foundation-sites/bin/ldap-add.sh $username $uid $nthash $sshahash $shell $ldapemail 2>&1" ) );
            $ok = true;
            if( $success !== 'User added ok' ) {
                throw new fValidationException( '<p>An unknown error ocurred while creating the LDAP account, please contact IRC.'. htmlspecialchars($success) .'</p>' );
            } else {
                $user->setLdapuser($_POST['ldapuser']);
                $user->setLdapnthash($_POST['ldapnthash']);
                $user->setLdapsshahash($_POST['ldapsshahash']);
                $user->setLdapshell($_POST['ldapshell']);
                $user->setLdapemail($_POST['ldapemail']);
                $user->store();
            }
        } catch (fValidationException $e) {
            $error = $e->getMessage();
        } catch (fSQLException $e) {
            $error = "<p>An unexpected LDAP error occurred, please contact IRC.</p>";
            trigger_error( $e->getMessage() );
        }
        if ($ok == true) {
            echo '<script>all_ok("LDAP account setup successfully!");</script>';
        }
    }
?>
    <p>As a member of London Hackspace you can have an entry in our <a href="https://wiki.london.hackspace.org.uk/view/LDAP">LDAP database</a></a>.</p>

    <p>This will allow you to log into our various servers, desktops, CNC workstations, and use a <a href="https://spacefed.net/index.php?title=Spacenet">federated wifi system</a> that's linked to multiple hackspaces across the globe.</p>

    <p>There are many systems that can talk to an LDAP database, we will expand this to include other services in the future.</p>

    <p>There are 2 password fields below beacause the NTPassword hash is very weak. Unfortunately you have to use it for SpaceNet. Note that it will only be used for SpaceNet!</p>

    <p>It's probably a good idea for the passwords here to be different from the password for this website.</p>

    <p>We also include an email address in the LDAP database, it does not have to be the same as your main hackspace one. It's used for things like emails from any cron tasks you set up on a machine you have an account on.</p>

    <p>If you allow other members to see your main email address then LDAP will use that.</p>

    <!--
        irc_nick is null for all users and has no ui to edit
        nickname is for the door announcer.
    -->

    <?
    if (isset($error)) {
        echo '<script>bad_things(' . json_encode($error) . ');</script>';
    }
    ?>

    * <input id="username"      type=text     size=32 value="<? echo htmlspecialchars($user->getLdapuser()); ?>"><label for="username">LDAP Username</label><br />
    * <input id="ssha_password" type=password size=32><label for="ssha_password">Password for general use (Will be converted to an SSHA hash).</label><br />
    * <input id="nt_password"   type=password size=32><label for="nt_password">Password for spacefed wifi (Will be converted to an NTLMv2 hash).</label><br />
    <select id="shell">
    <?
    foreach ($shells as $shell) {
        if ($user->getLdapshell() === $shell) {
            echo '<option value="'.$shell.'" selected>'.$shell.'</option>';
        } else {
            echo '<option value="'.$shell.'">'.$shell.'</option>';
        }
    }
     ?>
    </select><label for="shell">Login shell</label><br />
    * <input id="email"      type=text     size=32 value="<? echo htmlspecialchars($email); ?>"><label for="username">LDAP Email address</label><br />

    <button onclick="calculateHashes()">Update LDAP account</button>

    <form method="POST" style="margin: 0;" id="ldapform">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <input type="hidden" name="ldapuser"     id="ldapuser" value="" />
    <input type="hidden" name="ldapnthash"   id="ldapnthash" value="" />
    <input type="hidden" name="ldapsshahash" id="ldapsshahash" value="" />
    <input type="hidden" name="ldapshell"    id="ldapshell" value="" />
    <input type="hidden" name="ldapemail"    id="ldapemail" value="" />
    <input type="hidden" name="create"       id="create" value="create" />
    </form>
<? } else { ?>
    <p>You must be a member to use this page.</p>
<?php }

require('../footer.php'); ?>
</body>
</html>
