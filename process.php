<?php

include ("connection.php");

if($_POST['submit']){
//////////////////////////////// User settings //////////////////
$numEpochs = $_POST['epoch']; 
$numHidden = $_POST['hidden'];
$LR_IH = $_POST['lr1'];
$LR_HO = $_POST['lr2'];
}

//////////////////////////////// Data dependent settings //////////////////
$numInputs = 31;
$numPatterns = 350;

////////////////////////////////////////////////////////////////////////////////

$patNum;
$errThisPat;
$outPred;
$RMSerror;

$trainInputs = array();
$trainOutput = array();


// the outputs of the hidden neurons

$hiddenVal = array();

// the weights
$weightsIH = array();
$weightsHO = array();


main();


//==============================================================
//********** THIS IS THE MAIN PROGRAM **************************
//==============================================================

function main()
{
 global $numEpochs;
 global $numPatterns;
 global $patNum;
 global $RMSerror;

 // initiate the weights
  initWeights();

 // load in the data
  initData();

 // train the network
    for($j = 0;$j <= $numEpochs;$j++)
    {

        for($i = 0;$i<$numPatterns;$i++)
        {

            //select a pattern at random
	    //srand();	
            $patNum = rand(0,$numPatterns-1);		 	   	

            //calculate the current network output
            //and error for this pattern
            calcNet();

            //change network weights
            WeightChangesHO();
            WeightChangesIH();
        }

        //display the overall network error
        //after each epoch
        calcOverallError();

        echo "epoch = ".$j."  MSError = ".$RMSerror."</br>";

    }

    //training has finished
    //display the results
    displayResults();

 }

//============================================================
//********** END OF THE MAIN PROGRAM **************************
//=============================================================






//***********************************
function calcNet()
{
 global $numHidden;
 global $hiddenVal;
 global $weightsIH;
 global $weightsHO;
 global $trainInputs;
 global $trainOutput;
 global $numInputs;
 global $patNum;
 global $errThisPat;
 global $outPred;


 //calculate the outputs of the hidden neurons
 //the hidden neurons are tanh

 for($i = 0;$i<$numHidden;$i++)
 {
  $hiddenVal[$i] = 0.0;

  for($j = 0;$j<$numInputs;$j++)
   {
    $hiddenVal[$i] = $hiddenVal[$i] + ($trainInputs[$patNum][$j] * $weightsIH[$j][$i]);
   }

   $hiddenVal[$i] = tanh($hiddenVal[$i]);

 }

 //calculate the output of the network
 //the output neuron is linear
   $outPred = 0.0;

   for($i = 0;$i<$numHidden;$i++)
   {
    $outPred = $outPred + $hiddenVal[$i] * $weightsHO[$i];
   }
    //calculate the error
    $errThisPat = $outPred - $trainOutput[$patNum];
 }


//************************************
 function WeightChangesHO()
 //adjust the weights hidden-output
 {
  global $numHidden;
  global $LR_HO;
  global $errThisPat; 
  global $hiddenVal;
  global $weightsHO;

   for($k = 0;$k<$numHidden;$k++)
   {
    $weightChange = $LR_HO * $errThisPat * $hiddenVal[$k];
    $weightsHO[$k] = $weightsHO[$k] - $weightChange;
   }
 }


//************************************
 function WeightChangesIH()
 //adjust the weights input-hidden
 {
  global $trainInputs;
  global $numHidden;
  global $numInputs;
  global $hiddenVal;
  global $weightsHO;
  global $weightsIH;
  global $LR_IH;
  global $patNum;
  global $errThisPat; 

  for($i = 0;$i<$numHidden;$i++)
  {
   for($k = 0;$k<$numInputs;$k++)
   {
    $x = 1 - ($hiddenVal[$i] * $hiddenVal[$i]);
    $x = $x * $weightsHO[$i] * $errThisPat * $LR_IH;
    $x = $x * $trainInputs[$patNum][$k];
    $weightChange = $x;
    $weightsIH[$k][$i] = $weightsIH[$k][$i] - $weightChange;
   }
  }
 }


//************************************
 function initWeights()
 {
  global $numHidden;
  global $numInputs;
  global $weightsIH;
  global $weightsHO;

  for($j = 0;$j<$numHidden;$j++)
  {
    $weightsHO[$j] = (rand()/32767 - 0.5)/2;
    for($i = 0;$i<$numInputs;$i++)
    {
    $weightsIH[$i][$j] = (rand()/32767 - 0.5)/5;
    }
  }

 }


//************************************
 function initData()
 {
  global $trainInputs; 
  global $trainOutput;
  global $patNum;
  
	//get the data from database
	$select="select 
					FIELD1, FIELD2, FIELD3, FIELD4, FIELD5, FIELD6, FIELD7, FIELD8, FIELD9, FIELD10,
					FIELD11, FIELD12, FIELD13, FIELD14, FIELD15, FIELD16, FIELD17, FIELD18, FIELD19, FIELD20,
					FIELD21, FIELD22, FIELD23, FIELD24, FIELD25, FIELD26, FIELD27, FIELD28, FIELD29, FIELD30 
			 from bpr";
	$brs= mysql_query($select);
	while ($rowi = mysql_fetch_array($brs)) 
	{
   		 array_push($trainInputs,$rowi);
	}
	
	$query="select FIELD31 from bpr";
	$mysql= mysql_query($query);
	while($row =  mysql_fetch_array($mysql)) 
	{
    	$trainOutput[] = $row['FIELD31'];
	}
	
	for ($i = 0; $i <= $patNum; $i++) 
	{
	  for ($j = 0; $j <= 30; $j++)
	  {
			$trainInputs[$i][$j];
			$trainInputs[$i][32]=1; //weight of bias
			$trainOutput[$i];
	  }
	}
 	
}


//************************************
 function displayResults()
 {
  global $numPatterns;
  global $patNum;
  global $outPred;
  global $trainOutput;
  global $weightsIH;
  global $weightsHO;

  for($i = 0;$i<$numPatterns;$i++)
   {
    $patNum = $i;
    calcNet();
    echo "</br>";
	print "Data Learning Outputs Number-[$i] = ".$trainOutput[$patNum]." Desired Output = ".round($outPred,6)."</br>";
   }
   	echo "</br>================================================================</br>";
	echo "Last Weight Improvement for Input-Hidden Layer (Weight of V): ";
	echo "</br>================================================================</br>";
	echo "</br>";
	echo "<pre>";
	print_r($weightsIH);
	echo "</pre></br>";
	echo "</br>================================================================</br>";
	echo "Last Weight Improvement for Input-Hidden Layer (Weight of W): ";
	echo "</br>================================================================</br>";
	echo "</br>";
	echo "<pre>";
	print_r($weightsHO);
	echo "</pre>";
 }


//************************************
function calcOverallError()
{
 global $numPatterns;
 global $patNum;	
 global $errThisPat;
 global $RMSerror;	

 $RMSerror = 0.0;
 for($i = 0;$i<$numPatterns;$i++)
  {
   $patNum = $i;
   calcNet();
   $RMSerror = $RMSerror + ($errThisPat * $errThisPat);
  }
   $RMSerror = $RMSerror/$numPatterns;
   $RMSerror = sqrt($RMSerror);
 }


?>
