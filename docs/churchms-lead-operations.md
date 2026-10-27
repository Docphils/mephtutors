# Church CMS lead handling

## Access and use

Only authenticated, email-verified users with the existing `admin` role can open the lead manager. The public form stores church representative and enquiry details only; it does not have fields for church members, credentials, pastoral information, or church financial records. Reply consent and optional marketing consent are separate. Administrator contact attempts are recorded by outcome without storing outbound message content. Notes and activity history are visible only in the authenticated admin manager.

## Correction, deletion, and anonymization requests

The contact can request correction or deletion through MephEd's contact page. An administrator should verify the requester using the contact details already on the lead before changing or anonymizing it. The admin lead detail page supports correction of core contact fields and anonymization of personal details. Anonymization removes contact/enquiry details and free-text activity descriptions while retaining activity type, author, and timestamp for operational audit. Handle any legal retention obligations before honoring deletion or anonymization.

## Retention decision required before production

The source brief does not specify a retention period, so this implementation does not automatically delete or anonymize records by age. MephEd must name the retention owner and approve a period for unconverted leads before production launch. Until that policy is approved, administrators should review old leads and handle requests individually under applicable obligations. Do not treat indefinite storage as the approved policy.

## Release choices

- Lead viewing and management use the existing `admin` role.
- CSV export and new-lead email notifications are not enabled.
- Phone is optional; form collection is open to all countries/regions.
- Follow-up and contact are manual in the admin interface.
