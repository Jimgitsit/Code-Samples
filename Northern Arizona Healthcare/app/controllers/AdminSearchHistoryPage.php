<?php

class AdminSearchHistoryPage extends BasePageController {
	
	public function defaultAction() {
		
		$em = $this->getEntityManager();
		$qb = $em->createQueryBuilder()
			->select('q')
			->from('SearchQuery', 'q')
			->orderBy('q.date', 'desc')
			->setMaxResults(100);
		$this->twigVars['queries'] = $qb->getQuery()->getArrayResult();
		
		// All time
		$qb2 = $em->createQueryBuilder()
			->select('count(q) as count', 'q.phrase')
			->from('SearchQuery', 'q')
			->groupBy('q.phrase')
			->setMaxResults(10)
			->orderBy('count', 'desc');
		$this->twigVars['topAllTime'] = $qb2->getQuery()->getArrayResult();
		
		// Last 7 days
		$sql = "SELECT phrase, COUNT(*) AS count FROM nahds.search_queries WHERE date(`date`) >= DATE(NOW()) - INTERVAL 1 WEEK GROUP BY phrase ORDER BY count DESC LIMIT 10";
		$rsm = new \Doctrine\ORM\Query\ResultSetMapping();
		$rsm->addScalarResult('phrase', 'phrase');
		$rsm->addScalarResult('count', 'count');
		$query = $em->createNativeQuery($sql, $rsm);
		$this->twigVars['topWeek'] = $query->getArrayResult();
		
		// Today
		$sql = "SELECT phrase, COUNT(*) AS count FROM nahds.search_queries WHERE date(`date`) = DATE(NOW()) GROUP BY phrase ORDER BY count DESC LIMIT 10";
		$rsm = new \Doctrine\ORM\Query\ResultSetMapping();
		$rsm->addScalarResult('phrase', 'phrase');
		$rsm->addScalarResult('count', 'count');
		$query = $em->createNativeQuery($sql, $rsm);
		$this->twigVars['topToday'] = $query->getArrayResult();
		
		
		
		$this->twig->display('admin-search-history.twig', $this->twigVars);
	}
}