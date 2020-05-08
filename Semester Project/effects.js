function checkInput()
{
  var inputBox = document.getElementById("input");
  var selectedOption = document.querySelector('input[name = "actions"]:checked').value;

  var inputText = (inputBox.value).replace(/^\s+|\s+$/g, '');
  var firstWord = inputText.substr(0, inputText.indexOf(" "));

  if(selectedOption.toLowerCase() == 'create' && !(firstWord.toLowerCase() == 'create'))
  {
    alert('Please enter a CREATE statement or change your selected query action.');
    return false;  
  }
  else if (selectedOption.toLowerCase() == 'update' && !(firstWord.toLowerCase() == 'update')) 
  {
    alert('Please enter an UPDATE statement or change your selected query action.');
    return false;  
  }
  else if (selectedOption.toLowerCase() == 'query' && (firstWord.toLowerCase() == 'create' || firstWord.toLowerCase() == 'update')) 
  {
    alert('Please select the ' + firstWord.toUpperCase() + ' query opotion to enter this query.nn');
    return false;  
  }
  else
    return true;
}

function hide_new_db()
{
  document.getElementById('newdb').style.display = 'none';
}
function show_new_db()
{
  document.getElementById('newdb').style.display = 'block';
}

function show_sqlodbs()
{
  document.getElementById('sqlodbs').style.display = 'block';
  document.getElementById('sqldbs').style.display = 'none';
  document.getElementById('blank').style.display = 'none';
}
function show_sqldbs()
{
  document.getElementById('sqlodbs').style.display = 'none';
  document.getElementById('sqldbs').style.display = 'block';
  document.getElementById('blank').style.display = 'none';
}
function show_table_list()
{
  document.getElementById('tbl_list').style.display = 'block';

  var e = document.getElementById("sqldblist");
  console.log(e);
  var text = e.options[e.selectedIndex].text;
  console.log(text);
}

var coll = document.getElementsByClassName("collapsible");
var i;

for (i = 0; i < coll.length; i++) {
  coll[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
    if (content.style.maxHeight){
      content.style.maxHeight = null;
    } else {
      content.style.maxHeight = content.scrollHeight + "px";
    } 
  });
}