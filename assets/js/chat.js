$(function() {
  'use strict';

  if ($('.chat-content .chat-body').length) {
    const chatBodyScroll = new PerfectScrollbar('.chat-content .chat-body');
  }

  $( '.chat-list .chat-item' ).each(function(index) {
    $(this).on('click', function(){
        $('.chat-content').toggleClass('show');
    });
  });

  $('#backToChatList').on('click', function(index) {
    $('.chat-content').toggleClass('show');
  });

});