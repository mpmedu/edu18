"use strict";

xx.module('siteData', function (apod) {

  apod.extend({
    siteVars: {
      siteName: 'TestU',
      pageName: 'Home page'
    },

    constants: {
      OID: 'edu',
      VERSION: 117,
    },
    
    topmenu: [
      { nom: 'Open File', func: 'OpenFile' },          // 0
      { nom: 'Categories', func: 'Categories' },            // 1
      {
        nom: 'Settings',   // 2
        submenu: [
          { nom: 'Number of questions', func: 'NumberOfQuestions' },
          { nom: 'Time allowed', func: 'TimeAllowed' },
          { nom: 'Audio volume', func: 'AudioVolume' }
        ]
      },
    ]
  });

});
