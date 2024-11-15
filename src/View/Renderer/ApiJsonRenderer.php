<?php declare(strict_types=1);

namespace Next\View\Renderer;

use JsonSerializable;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Exception;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ModelInterface as Model;
use Next\Stdlib\JsonUnescaped as Json;
use Omeka\Api\Exception\ValidationException;
use Omeka\Api\Response;
use Traversable;

/**
 * JSON renderer for API responses.
 *
 * Manage Omeka < v4.1 and >= v4.1.
 */
class ApiJsonRenderer extends \Omeka\View\Renderer\ApiJsonRenderer
{
    protected $eventManager = null;

    public function render($model, $values = null)
    {
        $response = $model->getApiResponse();
        $exception = $model->getException();

        if ($response instanceof Response) {
            $payload = $response->getContent();
        } elseif ($exception instanceof ValidationException) {
            $errors = $exception->getErrorStore()->getErrors();
            $payload = ['errors' => $errors];
        } elseif ($exception instanceof \Exception) {
            $payload = ['errors' => ['error' => $exception->getMessage()]];
        } else {
            $payload = $response;
        }

        if (null === $payload) {
            return null;
        }

        $jsonpCallback = $model->getOption('callback');
        if (null !== $jsonpCallback) {
            // Wrap the JSON in a JSONP callback.
            $this->setJsonpCallback($jsonpCallback);
        }

        // Copy of \Laminas\View\Renderer\JsonRenderer::render(), but with
        // JsonUnescaped as encoding method in order to use a full pretty print
        // if wanted, in one step.

        /** @see \Laminas\View\Renderer\JsonRenderer::render() */

        $nameOrModel = &$payload;
        $values = null;
        $prettyPrint = null !== $model->getOption('pretty_print');

        // use case 1: View Models
        // Serialize variables in view model
        if ($nameOrModel instanceof Model) {
            if ($nameOrModel instanceof JsonModel) {
                $children = $this->recurseModel($nameOrModel, false);
                $this->injectChildren($nameOrModel, $children);
                $values = $nameOrModel->serialize();
                // Pretty print the JSON for json serialized outside.
                if ($prettyPrint) {
                    $values = Json::prettyPrint($values);
                }
            } else {
                $values = $this->recurseModel($nameOrModel);
                $values = Json::encode($values, false, ['prettyPrint' => $prettyPrint]);
            }

            if ($this->hasJsonpCallback()) {
                $values = $this->jsonpCallback . '(' . $values . ');';
            }
            return $this->renderReturn($model, $payload, $values);
        }

        // use case 2: $nameOrModel is populated, $values is not
        // Serialize $nameOrModel
        if (null === $values) {
            if (! is_object($nameOrModel) || $nameOrModel instanceof JsonSerializable) {
                $return = Json::encode($nameOrModel, false, ['prettyPrint' => $prettyPrint]);
            } elseif ($nameOrModel instanceof Traversable) {
                $nameOrModel = ArrayUtils::iteratorToArray($nameOrModel);
                $return = Json::encode($nameOrModel, false, ['prettyPrint' => $prettyPrint]);
            } else {
                $return = Json::encode(get_object_vars($nameOrModel), false, ['prettyPrint' => $prettyPrint]);
            }

            if ($this->hasJsonpCallback()) {
                $return = $this->jsonpCallback . '(' . $return . ');';
            }
            return $this->renderReturn($model, $payload, $return);
        }

        // use case 3: Both $nameOrModel and $values are populated
        throw new Exception\DomainException(sprintf(
            '%s: Do not know how to handle operation when both $nameOrModel and $values are populated',
            __METHOD__
        ));
    }

    protected function renderReturn($model, $payload, $output)
    {
        if (!$this->eventManager) {
            return $output;
        }

        // Allow modules to return custom output.
        $args = $this->eventManager->prepareArgs([
            'model' => $model,
            'payload' => $payload,
            'format' => $this->format,
            'output' => $output,
        ]);
        $this->eventManager->trigger('api.output.serialize', $this, $args);
        return $args['output'];
    }
}
