<?php namespace Craft;

class ArtVandelayController extends BaseController
{
	public function actionIndex()
	{
		$this->renderTemplate('artVandelay/_index', array(
			'groupOptions'   => $this->_getGroupOptions(),
			'sectionOptions' => $this->_getSectionOptions()
		));
	}

	public function actionImport()
	{
		$this->requirePostRequest();

		$json = craft()->request->getParam('data', '{}');
		$data = json_decode($json);

		if ($data)
		{
			$ok = true;

			if (property_exists($data, 'fields'))
			{
				$ok = $ok && craft()->artVandelay->importFields($data->fields);
			}

			if (property_exists($data, 'sections'))
			{
				$ok = $ok && craft()->artVandelay->importSections($data->sections);
			}

			if ($ok)
			{
				craft()->userSession->setNotice('All done.');
				$this->redirectToPostedUrl();

				return;
			}
		}

		// TODO: tell the folks what actually went wrong
		craft()->userSession->setError('Get *out*! Invalid input data.');
		craft()->urlManager->setRouteVariables(array(
			'groupOptions'   => $this->_getGroupOptions(),
			'sectionOptions' => $this->_getSectionOptions()
		));
	}

	public function actionExport()
	{
		$this->requirePostRequest();

		$result = array(
			'fields'   => $this->_exportFields(),
			'sections' => $this->_exportSections()
		);

		$json = json_encode($result, JSON_PRETTY_PRINT);

		if (craft()->request->getParam('download'))
		{
			HeaderHelper::setDownload('export.json', strlen($json));
		}

		JsonHelper::sendJsonHeaders();
		echo $json;
		craft()->end();
	}

	private function _getGroupOptions()
	{
		$groupOptions = array();

		foreach (craft()->fields->getAllGroups() as $group)
		{
			$groupOptions[$group->id] = $group->name;
		}

		return $groupOptions;
	}

	private function _getSectionOptions()
	{
		$sectionOptions = array();

		foreach (craft()->sections->getAllSections() as $section)
		{
			$sectionOptions[$section->id] = $section->name;
		}

		return $sectionOptions;
	}

	private function _exportFields()
	{
		$selectedGroups = craft()->request->getParam('selectedGroups', '*');

		if ($selectedGroups == '*')
		{
			$groups = craft()->fields->getAllGroups();
		}
		else
		{
			$groups = array();

			foreach ($selectedGroups as $id)
			{
				$groups[] = craft()->fields->getGroupById($id);
			}
		}

		return craft()->artVandelay->exportFields($groups);
	}

	private function _exportSections()
	{
		return array();
	}
}