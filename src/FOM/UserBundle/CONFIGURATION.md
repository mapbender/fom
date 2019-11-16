# Extension configuration
FOM UserBundle evaluates the extension configuration node `fom_user`.
The defaults are:
```yaml
fom_user:
    mail_from_address: ~
    mail_from_name: ~

    # Self-service password reset
    reset_password: true
    ## Minimum time (in hours) between resets
    max_reset_time: 24

    # Public account registration
    selfregister: false
    ## Maximum time (in hours) before registration token expires
    max_registration_time: 24
    ## Titles of groups self-registered users will be assigned to
    self_registration_groups: []

    # User metadata customization
    ## PHP class name of user profile entity
    profile_entity: FOM\UserBundle\Entity\BasicProfile
    ## PHP class name of user profile form type
    profile_formtype: FOM\UserBundle\Form\Type\BasicProfileType
    ## Twig resource path to user profile template
    profile_template: FOMUserBundle:User:basic_profile.html.twig

    # Artificial login delay after repeated failed attempts
    ## Login delay (in seconds) for repeated failed attempts
    login_delay_after_fail: 2
    ## Allowed login failures without adding delays
    login_attempts_before_delay: 3
    ## Time window for remembering past login failurs (PHP DateTimeInterval format)
    login_check_log_time: "-5 minutes"
```

A non-empty `mail_from_adress` is a prerequisite for sending system mails. Password reset and public
registration both use the `mail_from_adress`. If `mail_from_adress` is empty, these features cannot
be activated and the associated controller routes will emit `HTTP 404 - Not Found` errors.

Groups referenced by `self_registration_groups` will _not_ be added to the system automatically.
Nonexisting groups will be skipped, producing only a log message. If you want the assignments to work,
you will need to add the groups to the system backend first.
