export class FormBuilder {
  constructor () {
    var $formHolder = $('.widget-formbuilder-form')

    if ($formHolder.length === 0) {
      return false
    }

    if ($(window).width() <= 960) {
      return false
    }

    var $form = $formHolder.find('form')
    var $formRows = $form.find('.row')

    // we have more fields, so we split in two columns
    if ($formRows.length > 5) {
      var i = 0
      var columnIndex = 0
      var steps = 4
      var $formRow = $('<div id="formRow" class="row"/>')
      $form.append($formRow)

      $formRows.each(function () {
        var bingo = (i === 0 || i === steps)
        var $paragraph = $(this)

        if (bingo) {
          columnIndex += 1
          $formRow.append($('<div class="col-12 col-md-5" id="column-' + columnIndex + '" class="column"></div>'))
        }

        i += 1

        $('#column-' + columnIndex).append($paragraph)
      })
    }
  }
};
