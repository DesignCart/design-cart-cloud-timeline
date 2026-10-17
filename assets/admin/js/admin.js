/**
 * Design Cart Cloud Timeline
 * Code: design-cart-cloud-timeline
 * Autor: Paweł Nosko — https://www.designcart.pl/pawel-nosko.html
 * Firma: Design Cart — https://www.designcart.pl/
 */

(function ($) {
  "use strict";

  var i18n = (window.dcctAdmin && window.dcctAdmin.i18n) || {};

  function bindMedia($scope) {
    $scope.find("[data-dcct-media]").each(function () {
      var $control = $(this);
      if ($control.data("dcctBound")) return;
      $control.data("dcctBound", true);

      var frame = null;

      $control.find(".dcct-media__select").on("click", function (e) {
        e.preventDefault();
        if (frame) {
          frame.open();
          return;
        }

        frame = wp.media({
          title: i18n.selectImage || "Wybierz zdjęcie",
          button: { text: i18n.useImage || "Użyj zdjęcia" },
          multiple: false,
        });

        frame.on("select", function () {
          var attachment = frame.state().get("selection").first().toJSON();
          var thumbUrl =
            attachment.sizes && attachment.sizes.medium
              ? attachment.sizes.medium.url
              : attachment.url;
          $control.find(".dcct-media__id").val(attachment.id);
          $control.find(".dcct-media__preview").html('<img src="' + thumbUrl + '" alt="">');
        });

        frame.open();
      });

      $control.find(".dcct-media__clear").on("click", function (e) {
        e.preventDefault();
        $control.find(".dcct-media__id").val("");
        $control.find(".dcct-media__preview").empty();
      });
    });
  }

  function reindexEntries() {
    $("#dcct-entries .dcct-entry").each(function (index) {
      var $entry = $(this);
      $entry.attr("data-index", index);
      $entry.find(".dcct-entry__num").text(index + 1);
      $entry.find("[name]").each(function () {
        var name = $(this).attr("name");
        if (!name) return;
        $(this).attr("name", name.replace(/entries\[\d+]/, "entries[" + index + "]"));
      });
    });
  }

  function addEntry() {
    var template = document.getElementById("dcct-entry-template");
    if (!template) return;

    var node = template.content.cloneNode(true);
    var $entry = $(node).find(".dcct-entry");
    $("#dcct-entries").append($entry);
    bindMedia($entry);
    reindexEntries();
  }

  function syncActiveSection() {
    var $active = $(".dc-tabs__btn.dc-active");
    var tab = $active.data("dc-tab") || "tab-author";
    var map = {
      "tab-author": "author",
      "tab-entries": "entries",
      "tab-settings": "settings",
    };
    $("#dcct-active-section").val(map[tab] || "author");
  }

  $(function () {
    bindMedia($(document));

    var $list = $("#dcct-entries");
    if ($list.length) {
      $list.sortable({
        handle: ".dcct-entry__handle",
        placeholder: "dcct-entry-placeholder",
        tolerance: "pointer",
        update: reindexEntries,
      });
    }

    $("#dcct-add-entry").on("click", function () {
      addEntry();
    });

    $list.on("click", ".dcct-entry__remove", function () {
      if (!window.confirm(i18n.confirmRemoveEntry || "Usunąć ten wpis?")) {
        return;
      }
      $(this).closest(".dcct-entry").remove();
      reindexEntries();
    });

    $(document).on("click", ".dcct-delete", function (e) {
      if (!window.confirm(i18n.confirmDelete || "Usunąć ten timeline?")) {
        e.preventDefault();
      }
    });

    $(document).on("click", ".dc-tabs__btn", function () {
      setTimeout(syncActiveSection, 0);
    });

    $("#dcct-form").on("submit", syncActiveSection);

    // Hero "Zapisz" uses form="dcct-form"; ensure click still submits when browser quirks block it.
    $(document).on("click", 'button[form="dcct-form"]', function (e) {
      var form = document.getElementById("dcct-form");
      if (!form) return;
      syncActiveSection();
      if (typeof form.requestSubmit === "function") {
        e.preventDefault();
        form.requestSubmit();
      }
    });
  });
})(jQuery);
