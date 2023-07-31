<?php

namespace Drupal\caixa_eletronico\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Routing;

/**
 * Provides the form for adding countries.
 */
class SaqueForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'caixa_eletronico_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {


  
	$form['valor'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Digite um número inteiro.(Casas decimais serão desconsideradas.)'),
      '#required' => TRUE,
      '#maxlength' => 20,
      '#default_value' => '',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#default_value' => $this->t('Sacar') ,
    ];
	

    return $form;

  }
  
   /**
   * {@inheritdoc}
   */
  //É necessário validar se o campo existe, se é vazio e se é numérico, antes de liberar as cédulas.
  public function validateForm(array & $form, FormStateInterface $form_state) {
       $field = $form_state->getValues();
	  try{
		$fields["valor"] = $field['valor'];
		if (!$form_state->getValue('valor') || empty($form_state->getValue('valor')) || !is_numeric($form_state->getValue('valor'))) {
            $form_state->setErrorByName('valor', $this->t('Digite um número válido!'));
        }
    }
    catch(Exception $ex){
      \Drupal::logger('dn_students')->error($ex->addMessage());
    }
		
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array & $form, FormStateInterface $form_state) {
	try{

    $field = $form_state->getValues();

    $valor = (int)$field['valor'];
    $valorTotal = $valor;
		$notas = '';
    $cedulas =  [100,50,10,5,2,1];
    $quantidade = 0;

    /*Para cada valor de cédula, o programa deverá validar se o valor digitado ou restante da divisão anterior é maior que o valor da cédula,
    Caso seja maior, dividimos o valor pelo valor da cédula para saber quantas dessa cédula serão entregues no saque
    Fazer esse processo da maior para a menor cédula nos retornará o menor número possível de cédulas.

    A iteração poderá ser finalizada caso o restante da divisão ou valor original for 0.
    */ 

    for($i =0;$i<count($cedulas);$i++){
      if($valor <= 0){
        break;
      }

      if($valor >= $cedulas[$i]){
        $notas = $notas.intdiv($valor,$cedulas[$i]).' Nota(s) de R$'.$cedulas[$i].',00<br>';
        $quantidade = $quantidade + intdiv($valor,$cedulas[$i]);
        $valor = $valor%$cedulas[$i];
      }

    }

		  \Drupal::messenger()->addMessage($this->t('Valor sacado: R$'.$valorTotal.',00<br>'.
                                                'Quantidade de notas: '.$quantidade.
                                                '<br>'.$notas));
		 
	} catch(Exception $ex){
		\Drupal::logger('dn_students')->error($ex->addMessage());
	}
    
  }

}