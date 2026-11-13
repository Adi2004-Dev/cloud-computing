$(function () {
  var $form = $('#regForm'), $err = $('#error'), $btn = $('#submitBtn');

  function esc(s) { return $('<div>').text(s).html(); }

  $form.on('submit', function (e) {
    e.preventDefault();
    $err.prop('hidden', true);
    $form.find('.invalid').removeClass('invalid');

    // Client-side validation
    var bad = false;
    $form.find('[required]').each(function () {
      var $f = $(this);
      if ($f.is(':radio')) return;
      if (!this.checkValidity()) { $f.addClass('invalid'); bad = true; }
    });
    if (!$form.find('input[name=gender]:checked').length) bad = true;
    if (bad) {
      $err.text('Please complete all fields correctly (highlighted in red).').prop('hidden', false);
      return;
    }

    $btn.prop('disabled', true).text('Submitting...');
    $.ajax({ url: 'submit.php', method: 'POST', data: $form.serialize(), dataType: 'json' })
      .done(function (res) {
        if (!res.success) {
          $err.text((res.errors || ['Submission failed.']).join(' ')).prop('hidden', false);
          return;
        }
        var d = res.data, rows = [
          ['Full name', d.name], ['Email', d.email], ['Phone', d.phone],
          ['Date of birth', d.dob], ['Gender', d.gender], ['Course', d.course],
          ['Institution', d.institution], ['Interests', d.interests || 'None selected'],
          ['Address', d.address]
        ];
        var html = rows.map(function (r) {
          return '<tr><th>' + r[0] + '</th><td>' + esc(r[1]) + '</td></tr>';
        }).join('');
        $('#summary').html(html);
        $('#refLine').text('Reference ' + d.ref + ' · submitted ' + d.submitted);
        $('#formView').prop('hidden', true);
        $('#resultView').prop('hidden', false);
      })
      .fail(function () {
        $err.text('Could not reach the server. Please try again.').prop('hidden', false);
      })
      .always(function () { $btn.prop('disabled', false).text('Submit registration'); });
  });

  $('#againBtn').on('click', function () {
    $form[0].reset();
    $('#resultView').prop('hidden', true);
    $('#formView').prop('hidden', false);
  });
});
