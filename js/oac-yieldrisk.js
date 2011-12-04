YUI().use('node', 'event','charts','intl', 'io', 'json-parse', 'selector-css3', 'node-load', 'querystring-stringify-simple', function(Y){
  var selinputs = Y.all(".oac-select"),
      irrigatedInput = Y.all('input[name="irrigated"]'),
      phaseInput = Y.all('input[name="ensophase"]'),
      phaseText = Y.all('.ensotext').getContent(),
      currentState = {},
      currentData = [],
      ajaxHandler = Y.one('#ajax-handler').get('value'),
      graphColors = {series:{}},
      targetTable = Y.one('#phenology-table-data'),
      targetLabel = Y.one('#current-display-indicator'),
      displaySeries = [],
      displayText = [],
      currentPhase = '',
      updateInputs = function(e) {
        var order=['crop', 'variety', 'loc', 'soil', 'nitrogen'],
            values = selinputs.get('value');
        Y.each(order, function(item, i){
          currentState[item] = values[i];
        });
        currentState.irrigated = '0';
        currentState.phase = phaseInput.filter(':checked')._nodes[0].value;
        currentState.route = 'getData';
        targetLabel.setContent(phaseText[currentState.phase-1]);
        Y.io( ajaxHandler, {
          data: currentState,
          on: {
            success: function( id, o, a ) {
              var raw = Y.JSON.parse(o.responseText);
              currentData = raw.data;
              targetTable.setContent(raw.html);
              drawGraph();
            }
          }
        });
      },
      drawGraph = function() {
        
        Y.one("#graph").empty();
        var mychart = new Y.Chart({
          dataProvider:currentData,
          render:"#graph",
          type: 'column',
          styles: graphColors,
          axes: {
            percentage:{
              type: "numeric",
              position: "left",
              keys: displaySeries,
              labelFormat: {suffix: '%'},
              title: 'Probability'
            }
          },
          horizontalGridlines: {
            styles: {
              line: {
                color: "#dad8c9"
              }
            }
          },
          verticalGridlines: {
            styles: {
              line: {
              color: "#dad8c9"
              }
            }
          },
          tooltip: {
            markerLabelFunction: function( catItem, valueItem, _itemIdx, _series, seriesIdx ) {
              return '<span style="text-decoration: underline;">'+displayText[seriesIdx]+'</span><br/>'+catItem.value+': '+valueItem.value+'%';
            }
          }
        });
        // Planting date filter here
        Y.each(pdateInput, function(item, index){
          mychart.getSeries('Day_'+(index+1)).set('visible', item.get('checked'));
        });
      },
      pdateInput;
      
  Y.one('#oac-user-input-panel').delegate('change', updateInputs, '.oac-input');
  Y.one('#planting-dates-list').delegate('change', drawGraph, 'input[name="planting_date"]');
  
  //Main loop
  Y.io(ajaxHandler+'?route=getPlantingDates',{
    on: {
      success: function(id, o, a) {
        Y.one('#planting-dates-list').setContent(o.responseText);
        _dayColors = Y.all('.div_input').getStyle('background-color');
        Y.each(_dayColors, function(item, index){
          displaySeries.push('Day_'+(index+1));
          graphColors['series']['Day_'+(index+1)] = {marker: { fill: { color: item } } };
        });
        pdateInput = Y.all('input[name="planting_date"]');
        displayText = Y.all('.div_input_text').getContent();
        updateInputs();
      }
    }
  });
});