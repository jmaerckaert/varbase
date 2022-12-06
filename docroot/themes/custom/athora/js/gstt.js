/**
 * @file
 * Behaviors for the Google Speech to Text.
 */
(function ($, _, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.gstt = {
    attach: function (context) {
      if (detect.parse(navigator.userAgent).browser.family === 'Chrome') {
        // Modal speech to text.
        $('span.speech-search').once("speech-search").click(function (e) {
          e.preventDefault();
          let $elem = $(this);
          enableSpeech($elem);
          $($elem).on('speechDataFinal', function (e, finalString) {
            $('#drupal-modal input.form-search').val(finalString).trigger("delayed_input");
          });
        });
        // Block speech to text.
        $('a.speech-search').once("speech-search").click(function (e) {
          e.preventDefault();
          let $elem = $(this);
          enableSpeech($elem);
          $($elem).on('speechDataFinal', function (e, finalString) {
            $('input.form-search').val(finalString);
            $('#ajax-search').click();
          });
        });

        /**
         *
         * @param $elem
         */
        function enableSpeech($elem) {
          // Add class to theme.
          $($elem).addClass('recording');

          // Stream audio.
          let bufferSize = 2048,
            AudioContext,
            context,
            processor,
            input,
            globalStream;

          let streamStreaming = false;

          // AudioStream constraints.
          const constraints = {
            audio: true,
            video: false
          };

          let options = {transports: ['polling']};
          if (drupalSettings.bootstrap.gstt_io_socket_path !== undefined){
            options.path = drupalSettings.bootstrap.gstt_io_socket_path;
          }
          const socket = io(drupalSettings.bootstrap.gstt_io_socket_url, options);

          let downSampleBuffer = function (buffer, sampleRate, outSampleRate) {
            if (outSampleRate === sampleRate) {
              return buffer;
            }
            if (outSampleRate > sampleRate) {
              throw "downsampling rate show be smaller than original sample rate";
            }
            var sampleRateRatio = sampleRate / outSampleRate;
            var newLength = Math.round(buffer.length / sampleRateRatio);
            var result = new Int16Array(newLength);
            var offsetResult = 0;
            var offsetBuffer = 0;
            while (offsetResult < result.length) {
              var nextOffsetBuffer = Math.round((offsetResult + 1) * sampleRateRatio);
              var accum = 0, count = 0;
              for (var i = offsetBuffer; i < nextOffsetBuffer && i < buffer.length; i++) {
                accum += buffer[i];
                count++;
              }

              result[offsetResult] = Math.min(1, accum / count) * 0x7FFF;
              offsetResult++;
              offsetBuffer = nextOffsetBuffer;
            }
            return result.buffer;
          };

          /**
           * Process microphone.
           */
          function microphoneProcess(e) {
            let left = e.inputBuffer.getChannelData(0);
            let left16 = downSampleBuffer(left, 44100, 16000);
            socket.emit('binaryData', left16);
          }

          /**
           * Stop recording if a final word is returned.
           */
          function stopRecording() {
            if (!streamStreaming) {
              return;
            } // Stop disconnecting if already disconnected.

            streamStreaming = false;
            socket.emit('endGoogleCloudStream', '');

            let track = globalStream.getTracks()[0];
            track.stop();

            input.disconnect(processor);
            processor.disconnect(context.destination);
            context.close().then(function () {
              input = null;
              processor = null;
              context = null;
              AudioContext = null;
            });
          }

          let lang = $.cookie('language');
          if (lang) {
            socket.emit('startGoogleCloudStream', {
              config: {
                languageCode: lang + '-BE',
                model: 'command_and_search',
              },
              interimResults: false,
              singleUtterance: true,
            }); //init socket Google Speech Connection
            streamStreaming = true;
            AudioContext = window.AudioContext || window.webkitAudioContext;
            context = new AudioContext({
              // if Non-interactive, use 'playback' or 'balanced' //
              // https://developer.mozilla.org/en-US/docs/Web/API/AudioContextLatencyCategory
              latencyHint: 'interactive',
            });
            processor = context.createScriptProcessor(bufferSize, 1, 1);
            processor.connect(context.destination);
            context.resume();

            let handleSuccess = function (stream) {
              globalStream = stream;
              input = context.createMediaStreamSource(stream);
              input.connect(processor);

              processor.onaudioprocess = function (e) {
                microphoneProcess(e);
              };
            };

            navigator.mediaDevices.getUserMedia(constraints)
              .then(handleSuccess);

            socket.on('speechData', function (data) {
              let dataFinal = (data.results[0] !== undefined) ? data.results[0].isFinal : false;

              if (dataFinal === true) {
                // Log final string.
                let finalString = data.results[0].alternatives[0].transcript;
                // Trigger custom event.
                $($elem).trigger('speechDataFinal', finalString);

                stopRecording();
                $($elem).removeClass('recording');
              }
            });
          }
        }
      }
      else {
        $('.stt-microphone').addClass('gstt-non-compatible');
      }
    }
  };
})
(window.jQuery, window._, window.Drupal, window.drupalSettings);
