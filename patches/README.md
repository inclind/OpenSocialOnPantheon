# Beebe Net Patches

## Core

 - Views TranslationLanguageRenderer fails on ghost nodes: https://www.drupal.org/project/drupal/issues/2869347
 
    https://www.drupal.org/files/issues/views-translation-language-renderer-2869347-2-D8.patch
### Content Moderation

- Dispatch events for changing content moderation states
  - https://www.drupal.org/project/drupal/issues/2873287
  - https://www.drupal.org/files/issues/2018-11-06/2873287-50.patch

### Views

- View output is not used for entityreference options.
  - https://www.drupal.org/files/issues/2019-06-21/2174633-309.patch
    - Updated Version Applied: https://www.drupal.org/files/issues/2019-08-08/drupal-use_view_output_for_entityreference_options-2174633-323.patch
  - https://www.drupal.org/project/drupal/issues/2174633
- Provide a relationship from the revision table to the main table.
  - https://www.drupal.org/files/issues/2019-01-17/2652652-53.patch
  - https://www.drupal.org/project/drupal/issues/2652652
- Entity reference field View output is not used for selected entity display
  - https://www.drupal.org/files/issues/2018-11-27/entityreference_autocomplete_views_edit-2796341-18.patch
  - https://www.drupal.org/project/drupal/issues/2796341
- 'view_path' is set to /views/ajax after second ajax request
  - https://www.drupal.org/files/issues/2018-03-14/drupal-views_path_ajax-2866386-10.patch
  - https://www.drupal.org/project/drupal/issues/2866386#comment-12525337
- (Not Applied) Allow Views output for Entity Reference fields.
  - https://www.drupal.org/files/issues/2019-08-08/drupal-use_view_output_for_entityreference_options-2174633-323.patch
  - https://www.drupal.org/project/drupal/issues/2174633

## Contrib Modules

### Group

- Issues with AJAX views based on Group permissions (contextual filters) /views/ajax?_wrapper_format=drupal_ajax HTTP/1.1 - HTTP/1.1 403 Forbidden
  - https://www.drupal.org/project/group/issues/2942657#comment-12729597
  - https://www.drupal.org/files/issues/2018-08-15/group-route-match-ajax-views-d8-2942657-11.patch

### Like & Dislike

- Unable to like/dislike via mobile apps. Needed Basic Authentication.
  - https://www.drupal.org/files/issues/2018-04-03/2956507-4.patch
  - https://www.drupal.org/project/like_and_dislike/issues/2956507

### Media Entity Download

- Allow for a persistent download link for Media entities.
  - https://www.drupal.org/files/issues/2018-11-12/force-download-3008834-8.patch
  - https://www.drupal.org/project/media_entity_download/issues/3008834

### Menu Block

- Was implemented to force the menu blocks to follow the active trail of menu links. Will likely be committed to next update of module.
  - https://www.drupal.org/project/menu_block/issues/2756675
  - https://www.drupal.org/files/issues/2018-08-08/menu_block-follow-2756675-55.patch

### Restrict IP

- Redirect to login page for authenticated user can cause redirect loop https://www.drupal.org/project/restrict_ip/issues/2905569
    - https://www.drupal.org/files/issues/2905569_check_user_authentication.patch
- PHP 7.3 Countable() issue - custom Inclind patch
    - inclind_custom-restrict_ip-countable_php_error-d8.patch

### Scheduled Updates

- Scheduled Updates does not work with Content Moderation: https://www.drupal.org/project/scheduled_updates/issues/2821916

  - https://www.drupal.org/files/issues/2018-07-31/scheduled_updates_with_content_moderation-2821916-28.patch

- Cron Updates not run by User #1 (includes 2 patches):
  - Inclind refactored, based on: Cron Updates not ran by User #1 https://www.drupal.org/node/2793489
    - inclind-scheduled_updates-refactored_2793489-20.patch
  - Column not found: 1054 Unknown column 'node_revision__publish_date.nid' in 'where clause': https://www.drupal.org/project/scheduled_updates/issues/2872239

    - https://www.drupal.org/files/issues/scheduled_updates-fix-all-revisions-queries-2872239-36.patch 

- The embedded update runner does not pick up all updates: https://www.drupal.org/project/scheduled_updates/issues/2820944

  - https://www.drupal.org/files/issues/update_runner_not_picking_all_updates-2820944-2.patch
- Call to a member function getRevisionId() on null: https://www.drupal.org/project/scheduled_updates/issues/2915066

  - https://www.drupal.org/files/issues/2018-08-09/2915066-1.patch
- If Scheduled update was queued on a revision, which is no longer the latest 
one, we will remove current scheduled update from queue, mark it "Un-run" and it
will be checked for the latest revision again next Cron run.
        !!! Custom Inclind Patch
  - inclind-scheduled_updates-latest_revision_schedule_check-d8.patch
- https://www.drupal.org/project/scheduled_updates/issues/3070625
    - "https://www.drupal.org/files/issues/2019-07-26/fix_access_control.patch",
- https://www.drupal.org/project/scheduled_updates/issues/3065544
    - "https://www.drupal.org/files/issues/2019-07-03/3065544_3.patch"

### Search API

- Custom Inclind - do not strip tags in search_api based views for Rendered HTML field
  - inclind-search_api-views_dont_strip_tags_rendered_html-d8.patch


### Views Ajax Get (views_ajax_get)

- By default was not pointed at the actual system performance config value. !!!!  Custom Inclind Patch
  - views_ajax_get/Adjust_the_configuration_key_to_reliably_pull_value_.patch


### Voting API (votingapi)

- Anonymous voting cannot be differentiated if IP is the same.  !!! Custom Inclind Patch based on
https://www.drupal.org/project/votingapi/issues/2791129

  - voting_api/votingapi-inclind_custom_fix_anonymous_voting_session.patch

## Open Social

### Entity Access By Field

- Unpublished content is not accessible for users with permission 'view any unpublished content'
  - https://www.drupal.org/project/social/issues/2905325
  - open_social/entity_access_by_field/unpublished-content-not-accessible-for-author-2905325-5.patch

### Social Base Theme

- One Column Layout in Social Base theme:
  https://www.drupal.org/project/social/issues/2907221
  
  Based on: https://www.drupal.org/files/issues/one-column-2907221-1.patch specifically
  - open_social/socialbase/opensocial_socialbase_theme_one_col_layout.patch
- "like_and_dislike" JS/Twig in Social Base theme - this is necessary because of like_and_dislike module upgrade to DEV of Sep 5, 2019
  - open_social/socialbase/inclind-socialbase_theme_after_like_dislike_upgrade.patch
- "Social Posts" module revisionable:
    - open_social/social_post/inclind_custom_-make_social_post_revisionable-d8.patch
