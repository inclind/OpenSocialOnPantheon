/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function (CKEDITOR) {
  function findElementByName(element, name) {
    if (element.name === name) {
      return element;
    }

    var found = null;
    element.forEach(function (el) {
      if (el.name === name) {
        found = el;

        return false;
      }
    }, CKEDITOR.NODE_ELEMENT);
    return found;
  }
  CKEDITOR.plugins.add('drupalimagestyle', {
    requires: 'drupalimage',

    beforeInit: function beforeInit(editor) {
      editor.on('widgetDefinition', function (event) {
        var widgetDefinition = event.data;
        if (widgetDefinition.name !== 'image') {
          return;
        }

        CKEDITOR.tools.extend(widgetDefinition.features, {
          drupalimagestyle: {
            requiredContent: 'img[data-image-style]'
          }
        }, true);

        var requiredContent = widgetDefinition.requiredContent.getDefinition();
        requiredContent.attributes['data-image-style'] = '';
        widgetDefinition.requiredContent = new CKEDITOR.style(requiredContent);
        widgetDefinition.allowedContent.img.attributes['!data-image-style'] = true;

        var originalDowncast = widgetDefinition.downcast;
        widgetDefinition.downcast = function (element) {
          var img = originalDowncast.call(this, element);
          if (!img) {
            img = findElementByName(element, 'img');
          }
          if (this.data.hasOwnProperty('data-image-style') && this.data['data-image-style'] !== '') {
            img.attributes['data-image-style'] = this.data['data-image-style'];
          }
          return img;
        };

        var originalUpcast = widgetDefinition.upcast;
        widgetDefinition.upcast = function (element, data) {
          if (element.name !== 'img' || !element.attributes['data-entity-type'] || !element.attributes['data-entity-uuid'] || element.attributes['data-cke-realelement']) {
            return;
          }

          data['data-image-style'] = element.attributes['data-image-style'];

          element = originalUpcast.call(this, element, data);

          return element;
        };

        CKEDITOR.tools.extend(widgetDefinition._mapDataToDialog, {
          'data-image-style': 'data-image-style'
        });
      }, null, null, 20);
    }
  });
})(CKEDITOR);