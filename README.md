# Password Policy Bundle

<p align="center">
<a href="https://travis-ci.org/despark/password-policy-bundle"><img src="https://travis-ci.org/despark/password-policy-bundle.svg?branch=master" alt="Build Status"></a>
</p>

### Installation
```bash
composer require despark/password-policy-bundle
```

### Configuration

1. Implement `Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface` in the entities
that you want to support password policies.

1. Implement `Despark\PasswordPolicyBundle\Model\PasswordHistoryInterface` in a new entity that will hold the password
history records.

1. Configure how Password policy will behave on every entity. Configuration example is [here](#configuration-example)

1. You need to add `@PasswordPolicy()` validation rules to your `$plainPassword` field

###### Configuration example:
```
password_policy:
    entities:
        # the entity class implementing HasPasswordPolicyInterface
        App\Entity\Participant:
            # The route where the user will be locked when password is expired
            lock_route: participant_profile

            # Params to pass to the locked param when build.
            lock_route_params:
                    locked: '1'

            # Which is the password property in the entity (defaults to 'password')
            password_field: ~

            # Password history property in the entity (default to 'passwordHistory')
            password_history_field: ~

            # How many password changes to track (defaults to 3)
            passwords_to_remember: ~

            # Force expiry of the password in that many days
            expiry_days: ~
        App\Entity\User:
            lock_route: admin_app_user_edit
            lock_route_params:
                    # {id} will paresed as current user id when possible
                    id: '{id}'
    expiry_listener:
            # You can change the expiry listener priority
            priority: 0
            error_msg:
                text: 'Your password expired. You need to change it'
                type: 'error'

        listener_priority: 0
        # The route that needs to be shown to the user when password is expired
        lock_route: participant_settings
        # These routes will be excluded from the expiry check
        excluded_routes: ~
```

##### Expiry
Expiry works by checking last password change on every request made to the app, excluding those configured in the application

##### Good to know
The library uses doctrine lifecycle events to create password history and set last password change on the target entities.
In order for this to happen we use the onFlush event and we are recalculating the history change set inside it.
You must be aware of that as any entity changes after the recalculation will not be persisted to the database.