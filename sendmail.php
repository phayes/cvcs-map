<?php
require_once("./sendgrid-php/sendgrid-php.php");

$grindpass = trim(file_get_contents("./.grindpass"));

$admins = array(
	'Patrick Hayes <patrick.d.hayes@gmail.com>',
);

$badwords = array("fuck", "shit", "cunt", "asshole", "bitch", "damn", "stupid", "idiot");

$representatives = array(
	'region' => array(
		'Director Edwin Grieve <edwingrieve@shaw.ca>',
		'Director Rodney Nicol <rodnichol@shaw.ca>',
		'Director Bruce Jolliffe<bjolliffe@comoxvalleyrd.ca>', 
	), 
	'courtenay' => array(
		'Mayor Larry Jangula <ljangula@courtenay.ca>',
		'Councillor Erik Eriksson <eeriksson@courtenay.ca>',
		'Councillor David Frisch <dfrisch@courtenay.ca>',
		'Councillor Doug Hillian <dhillian@courtenay.ca>',
		'Councillor Rebecca Lennox <rlennox@courtenay.ca>',
		'Councillor Manno Theos <mtheos@courtenay.ca>',
		'Councillor Bob Wells <bwells@courtenay.ca>',
	), 
	'comox' => array(
		'Mayor Paul Ives <pives@comox.ca>',
		'Councillor Russ Arnott <rarnott@comox.ca>',
		'Councillor Ken Grant <kgrant@comox.ca>',
		'Councillor Marg Grant <mgrant@comox.ca>',
		'Councillor Hugh MacKinnon <hmackinnon@comox.ca>',
		'Councillor Barbara Price <bprice@comox.ca>',
		'Councillor Maureen Swift <mswift@comox.ca>', 
	),
	'cumberland' => array(
		'Mayor Leslie Baird <mayor.baird@cumberland.ca>',
		'Councillor Jesse Ketler <councillor.ketler@cumberland.ca>',
		'Councillor Roger Kishi <councillor.kishi@cumberland.ca>',
		'Councillor Gwyn Sproule <councillor.sproule@cumberland.ca>',
		'Councillor Sean Sullivan <councillor.sullivan@cumberland.ca>',
	),
	'outside' => array(
		'Director Edwin Grieve <edwingrieve@shaw.ca>',
		'Director Rodney Nicol <rodnichol@shaw.ca>',
		'Director Bruce Jolliffe<bjolliffe@comoxvalleyrd.ca>',
		'Director Ken Grant <kgrant@comox.ca>',
		'Director Barbara Price <bprice@comox.ca>',
		'Director Larry Jangula <ljangula@courtenay.ca>',
		'Director Erik Eriksson <eeriksson@courtenay.ca>',
		'Director Manno Theos <mtheos@courtenay.ca>',
		'Director Bob Wells <bwells@courtenay.ca>',
		'Director Gwyn Sproule <councillor.sproule@cumberland.ca>',
	)
);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$html = file_get_contents('./sendtemplate.html');
	print $html;
	exit;
}
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (empty($_POST['name'])) {
		print "Invalid name";
		exit;
	}
	if (empty($_POST['email'])) {
		print "Invalid email";
		exit;
	}
	if (empty($_POST['email'])) {
		print "Invalid email";
		exit;
	}
	if (!array_key_exists($_POST['location'], $representatives)) {
		print "Invalid location";
		exit;
	}
	if (empty($_POST['subject'])) {
		print "Invalid subject";
		exit;
	}

	// Basic passes passed, check for a rejected item that will still forward to us with tag "REJECTED"
	$rejected = FALSE;

	$letter = $_POST['letter'];

	if (count($letter) < 200) {
		$rejected = "Too Short";
	}

	foreach ($badwords as $badword) {
		if (stripos($letter, $badword) !== FALSE) {
			$rejected = "Found bad word `$badword`";
		}
		if (stripos($_POST['subject'], $badword) !== FALSE) {
			$rejected = "Found bad word `$badword`";
		}		
	}

	$sendgrid = new SendGrid('phayes', $grindpass);
	$email = new SendGrid\Email();

	foeach ($admins as $admin) {
		$email->addBcc($admin);
	}

	if (!$rejected) {
		foreach ($representatives[$_POST['location']] as $rep) {
			$email->addTo($rep);
		}
	}

	$email->setFrom($_POST['email']);

	if (!$rejected) {
		$email->setSubject($_POST['subject']);
	}
	else {
		$email->setSubject('REJECTED: ' . $rejected . ' -- ' . $_POST['subject']);
	}

	// Add unsubscribe message
	$letter .= "\n\n\n\n ------------------------ \n This message has been sent as part of a campaign by the Comox Valley Conservation Strategy. To opt-out of all future emails please send an email to patrick.d.hayes@gmail.com . "

	$email->setText($letter);

	$sent = $sendgrid->send($email);

	if (!$sent) {
		print "Sorry something went wrong sending the email. Please try again."
		exit;
	}
	else {
		print "Thank you so much for contacting your elected representative!"
		exit;
	}
}
