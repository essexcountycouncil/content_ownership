# Content Ownership
For specifying an entity for the creation/migration of Content Owners/Content SME

Content ownership is separate from authors. Owners and SME’s will not always be users in the CMS as they will not need to edit content. It should only be visible within the CMS, not the FE. 

In contentful, it is an entity with the below fields. All content has an owner, and this field will need to be on all content types. This new entity will need these to support migration: 

- Name - text (required)
- Email - email address
- Role - 'Content Owner' / 'SME'
- Notes - free text