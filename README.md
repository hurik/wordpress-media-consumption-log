# Media Consumption Log

## Table
This Table is needed:

```sql
CREATE TABLE `wp_mcl_finished` (
  `tag_id` bigint(20) unsigned NOT NULL,
  `cat_id` bigint(20) unsigned NOT NULL,
  `finished` tinyint(1) NOT NULL,
  PRIMARY KEY (`tag_id`,`cat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
```

## Google Charts Options
Default value:

```javascript
height: data.getNumberOfRows() * 15 + 100,
legend: { position: 'top', maxLines: 4, alignment: 'center' },
bar: { groupWidth: '70%' },
focusTarget: 'category',
chartArea:{left: 100, top: 80, width: '75%', height: data.getNumberOfRows() * 15},
isStacked: true,
```

## Sources
- [Get Tags specific to Category](http://wordpress.org/support/topic/get-tags-specific-to-category) by various people in this thread
- [How to Add a Custom Button in WordPress Admin Bar](http://stanislav.it/how-to-add-a-custom-button-in-wordpress-admin-bar/) by Stanislav Kostadinov
- [Add tags to post before it's created](http://wordpress.stackexchange.com/a/134711) by Milo
- [WordPress: tags with commas and other / custom taxonomiesby](http://blog.foobored.com/all/wordpress-tags-with-commas/) foo bored
