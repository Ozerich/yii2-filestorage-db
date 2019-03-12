(function () {
  const isAdvancedUpload = function () {
    const div = document.createElement('div');
    return ( ( 'draggable' in div ) || ( 'ondragstart' in div && 'ondrop' in div ) ) && 'FormData' in window && 'FileReader' in window;
  }();

  $.fn.imageInput = function (options) {
    $(this).each(function () {
      return new ImageWidget($(this), options);
    });
  };

  function ImageWidget($fileInput, options) {
    const that = this;

    options = Object.assign({
      uploadUrl: '/upload'
    }, options || {});

    const $container = $fileInput.parents('.widget-image');
    if ($container.length === 0) {
      return;
    }

    const $containerInput = $container.find('.widget-image__empty');
    const $containerImage = $container.find('.widget-image__model');

    let loading = false;

    $fileInput.on('change', function () {
      that.upload($fileInput.get(0).files[0]);
    });

    $container.find('.js-widget-image-delete').on('click', function () {
      that.onDeleteClick();
      return false;
    });

    this.setValue = function (value) {
      $container.find('input[type=hidden]').val(value);
    };

    this.upload = function (file) {
      const formData = new FormData();
      formData.append('file', file);

      this.setLoading(true);

      $.ajax({
        type: 'POST',
        url: options.uploadUrl,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {
          that.onSuccess(data.image);
          that.setLoading(false);
        },
        error: function (data) {
          if (data.status !== 400) {
            that.onError(data.responseJSON ? data.responseJSON.message : data.responseText);
          } else {
            that.onError(data.responseJSON.error);
          }
          that.setLoading(false);
        }
      });
    };

    this.onDeleteClick = function () {
      $containerImage.addClass('_hidden');
      $containerInput.removeClass('_hidden');

      that.setValue(null);
    };

    this.setLoading = function (value) {
      loading = !!value;
      $container.toggleClass('_loading', loading);
      $container.find('.widget-image__text').toggle(!loading);
      $container.find('.widget-image__loading').toggle(loading);
    };

    this.onSuccess = function (model) {
      $containerInput.addClass('_hidden');
      $containerImage.find('img').attr('src', model.url);
      $containerImage.removeClass('_hidden');

      that.setValue(model.id);
    };

    this.onError = function (errorMessage) {
      alert(errorMessage);
    };

    this.initDragAndDrop = function () {
      ['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach(function (event) {
        $containerInput.get(0).addEventListener(event, function (e) {
          e.preventDefault();
          e.stopPropagation();
        });
      });

      ['dragover', 'dragenter'].forEach(function (event) {
        $containerInput.get(0).addEventListener(event, function () {
          $containerInput.get(0).classList.add('is-dragover');
        });
      });

      ['dragleave', 'dragend', 'drop'].forEach(function (event) {
        $containerInput.get(0).addEventListener(event, function () {
          $containerInput.get(0).classList.remove('is-dragover');
        });
      });

      $containerInput.get(0).addEventListener('drop', function (e) {
        const droppedFiles = e.dataTransfer.files;
        if (droppedFiles.length > 0) {
          that.upload(droppedFiles[0]);
        }
      });
    };

    if (isAdvancedUpload) {
      this.initDragAndDrop();
    }
  }
})();